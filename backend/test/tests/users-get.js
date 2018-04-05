const test = require('../testlib');
const cartesianProduct = require('cartesian-product');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test sorting in all combinations
        const sortCombinations = cartesianProduct([
            ['', 'id-asc', 'id-desc'],
            ['', 'name-asc', 'name-desc'],
            ['', 'type-asc', 'type-desc'],
        ]);

        for (list of sortCombinations) {
            list = list.filter((str) => str.length > 0);
            var sortQuery = list.join(',');

            var res = await req({
                url: '/users?sort=' + sortQuery,
                method: 'get'
            });

            assert.equal(res.status, 200);

            var sortedData = res.data.results.slice();
            sortedData.sort(function (a, b) {
                for (sort of list) {
                    var spec = sort.split('-');
                    if (a[spec[0]] < b[spec[0]]) {
                        return spec[1] == 'asc' ? -1 : 1;
                    } else if (a[spec[0]] > b[spec[0]]) {
                        return spec[1] == 'asc' ? 1 : -1;
                    }
                }
                return 0;
            });

            assert.equal(res.data.results, sortedData, 'Sort failed for ' + res.config.url);
        }

        //Test paging
        var res = await req({
            url: '/users?pagesize=2',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.paging, {
            page: 1,
            total: 2,
            pagesize: 2
        }, 'Paging data fail for ' + res.config.url);
        assert.equal(res.data.results.length, 2, "Should be 2 results.");

        var res = await req({
            url: '/users?pagesize=2&page=2',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.paging, {
            page: 2,
            total: 2,
            pagesize: 2
        }, 'Paging data fail for ' + res.config.url);
        assert.equal(res.data.results.length, 1, "Should be 2 results.");

        //Test query name
        var res = await req({
            url: '/users?query=user&sort=id-asc',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            {
                id: 2,
                name: 'user',
                type: 'user',
                native: true
            },
            {
                id: 3,
                name: 'config/configuser',
                type: 'user',
                native: false
            }
        ], 'Result fail for ' + res.config.url);

        //Type filter
        var res = await req({
            url: '/users?type=admin,user',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results.length, 3, 'Result fail for ' + res.config.url);

        //Type filter
        var res = await req({
            url: '/users?type=admin',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            {
                id: 1,
                name: 'admin',
                type: 'admin',
                native: true
            }
        ], 'Result fail for ' + res.config.url);

        //Query all check for format
        var res = await req({
            url: '/users?sort=id-asc',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            { id: 1, name: 'admin', type: 'admin', native: true },
            { id: 2, name: 'user', type: 'user', native: true },
            { id: 3, name: 'config/configuser', type: 'user', native: false }
        ], 'Result fail for ' + res.config.url);
    });

    await test('user', async function (assert, req) {
        //Type filter
        var res = await req({
            url: '/users',
            method: 'get'
        });

        assert.equal(res.status, 403, 'Get should fail for user');
    });
});