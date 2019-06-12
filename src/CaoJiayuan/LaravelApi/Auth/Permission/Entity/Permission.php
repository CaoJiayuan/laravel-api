<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/1/31
 * Time: 下午4:27
 */

namespace CaoJiayuan\LaravelApi\Auth\Permission\Entity;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Zizaco\Entrust\EntrustPermission;

/**
 * CaoJiayuan\LaravelApi\Auth\Permission\Entity\Permission
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string $path 菜单uri
 * @property string $icon
 * @property int $parent_id
 * @property bool $type 类型(0-菜单,1-操作)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entity\Role[] $roles
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereIcon($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission wherePath($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entity\Permission[] $children
 */
class Permission extends EntrustPermission
{
    public static $roles = [];

    protected $casts = [
        'granted' => 'bool',
    ];

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'path',
        'icon',
        'parent_id',
        'type',
    ];

    public function node()
    {
        $children = $this->children();
        $prefix = \DB::getTablePrefix();
        $table = $this->getTable();
        $su = Config::get('entrust.administrator_name', 'administrator');;

        $children->select([
            $table . '.*',
        ]);
        $children->groupBy($table . '.id');
        /** @var Role[] $roles */
        $roles = static::$roles;
        $ids = [];
        foreach ($roles as $role) {
            $ids[] = data_get($role, 'id');
        }

        $permissionRoleTable = Config::get('entrust.permission_role_table', 'permission_role');
        $children->leftJoin($permissionRoleTable, function (JoinClause $clause) use ($ids, $permissionRoleTable, $table) {
            $clause->on($permissionRoleTable . '.permission_id', '=', $table . '.id');
            $clause->whereIn($permissionRoleTable . '.role_id', $ids);
        });


        $select = \DB::raw("CASE WHEN {$prefix}{$permissionRoleTable}.role_id IS NOT NULL THEN true ELSE false END AS granted");
        foreach ($roles as $role) {
            if (data_get($role, 'name') == $su) {
                $select = \DB::raw("true AS granted");
                break;
            }
        }
        $children->addSelect(\DB::raw($select));
        $this->beforeNode($children);
        return $children->with('node');
    }

    /**
     * @param Builder $query
     */
    public function beforeNode($query)
    {

    }

    public static function tree($roles, \Closure $nodeResolver = null, \Closure $treeResolver = null)
    {
        $prefix = \DB::getTablePrefix();
        $table = (new static())->getTable();
        $permissionRoleTable = Config::get('entrust.permission_role_table', 'permission_role');;
        $su = Config::get('entrust.administrator_name', 'administrator');;
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = static::where('parent_id', 0)->with(['node' => function ($builder) use($nodeResolver) {
            $nodeResolver && $nodeResolver($builder);
        }]);
        $builder->select([
            $table . '.*',
        ]);
        $builder->groupBy($table . '.id');
        $ids = [];
        static::$roles = $roles;

        foreach ($roles as $role) {
            $ids[] = data_get($role, 'id');
        }
        $builder->leftJoin($permissionRoleTable, function (JoinClause $clause) use ($ids, $permissionRoleTable, $table) {
            $clause->on($permissionRoleTable . '.permission_id', '=', $table . '.id');
            $clause->whereIn($permissionRoleTable . '.role_id', $ids);
        });
        $select = \DB::raw("CASE WHEN {$prefix}{$permissionRoleTable}.role_id IS NOT NULL THEN true ELSE false END AS granted");
        foreach ($roles as $role) {
            if (data_get($role, 'name') == $su) {
                $select = \DB::raw("true AS granted");
                break;
            }
        }

        $builder->addSelect($select);

        static::beforeTree($builder);
        $treeResolver && $treeResolver($builder);
        return $builder->get();
    }

    /**
     * @param Builder $query
     */
    public static function beforeTree($query)
    {

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id', 'id');
    }

    public static function getByNames(array $names)
    {
        return static::whereIn('name', $names)->get();
    }

    public static function flattened($roles)
    {
        $table = (new static())->getTable();

        $ids = [];
        foreach ($roles as $role) {
            $ids[] = $role->id;
        }
        $prefix = \DB::getTablePrefix();
        $permissionRoleTable = Config::get('entrust.permission_role_table', 'permission_role');

        $builder = Permission::leftJoin('permission_role', function (JoinClause $clause) use ($ids, $permissionRoleTable) {
            $clause->on( $permissionRoleTable. '.permission_id', '=', 'permissions.id');
            $clause->whereIn($permissionRoleTable. '.role_id',  $ids);
        });
        $select = \DB::raw("CASE WHEN {$prefix}permission_role.{$permissionRoleTable} IS NOT NULL THEN true ELSE false END AS granted");
        $su = Config::get('entrust.administrator_name','administrator');
        foreach ($roles as $role) {
            if (Arr::get($role, 'name') == $su) {
                $select = \DB::raw("true AS granted");
                break;
            }
        }
        return $builder->select([
            $table. '.*',
            $select
        ])->get()->toArray();
    }
}
