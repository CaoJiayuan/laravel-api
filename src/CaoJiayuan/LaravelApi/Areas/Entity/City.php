<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/7/28
 * Time: 下午3:47
 */

namespace CaoJiayuan\LaravelApi\Areas\Entity;


use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property integer $id
 * @property integer $province_id 省份id
 * @property string $name 城市名称
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\City whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\City whereProvinceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\City whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\City whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\City whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entity\Area[] $areas
 */
class City extends Model
{
    use FindByName;

    protected $fillable = ['province_id', 'name'];


    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
