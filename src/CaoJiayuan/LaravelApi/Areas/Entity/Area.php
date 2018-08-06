<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/7/28
 * Time: 下午3:50
 */

namespace CaoJiayuan\LaravelApi\Areas\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property integer $id
 * @property integer $city_id 城市id
 * @property string $name 区县名称
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Area whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Area whereCityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Area whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Entity\Area whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Area extends Model
{
    use FindByName;

    protected $fillable = ['city_id', 'name'];
}
