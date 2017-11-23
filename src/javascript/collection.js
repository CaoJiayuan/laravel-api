let _ = require('lodash');

/**
 *
 * @param {Array} items
 * @constructor
 */
function Collection(items) {
    Array.call(this);
    _.isArray(items) && this.push(...items);
    this.items = items;
}

Collection.prototype = Object.create(Array.prototype);
Collection.prototype.constructor = Collection;

Collection.prototype.pluck = function (key) {
    let result = [];
    _.forEach(this.items, (item, index) => {
        if(_.isObject(item) && item.hasOwnProperty(key)) {
            result.push(item[key]);
        }
    });

    return new Collection(result);
};

Collection.prototype.toArray = function () {
    return _.toArray(this.items);
};

Collection.prototype.first = function () {
    return _.head(this.items);
};

Collection.prototype.last = function () {
    return _.last(this.items);
};

Collection.prototype.filter = function (cb) {
    return new Collection(_.filter(this.items, cb))
};

Collection.prototype.sortBy = function (iteratees) {
    return new Collection(_.sortBy(this.items, iteratees))
};

Collection.prototype.isEmpty = function () {
    return _.isEmpty(this.items);
};

module.exports = Collection;