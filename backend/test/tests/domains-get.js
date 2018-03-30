const test = require('../testlib');
const cartesianProduct = require('cartesian-product');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //GET /domains?page=5&pagesize=10&query=foo&sort=id-asc,name-desc,type-asc,records-asc&type=MASTER

        //Test sorting in all combinations
        const sortCombinations = cartesianProduct([
            ['', 'id-asc', 'id-desc'],
            ['', 'name-asc', 'name-desc'],
            ['', 'type-asc', 'type-desc'],
            ['', 'records-asc', 'records-desc']
        ]);

        for (list of sortCombinations) {
            list = list.filter((str) => str.length > 0);
            var sortQuery = list.join(',');

            var res = await req({
                url: '/domains?sort=' + sortQuery,
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
            url: '/domains?pagesize=3',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.paging, {
            page: 1,
            total: 2,
            pagesize: 3
        }, 'Paging data fail for ' + res.config.url);
        assert.equal(res.data.results.length, 3, "Should be 3 results.");

        var res = await req({
            url: '/domains?pagesize=3&page=2',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.paging, {
            page: 2,
            total: 2,
            pagesize: 3
        }, 'Paging data fail for ' + res.config.url);
        assert.equal(res.data.results.length, 2, "Should be 2 results.");

        //Test query
        var res = await req({
            url: '/domains?query=.net&sort=id-asc',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            {
                id: 2,
                name: 'slave.example.net',
                type: 'SLAVE',
                master: '12.34.56.78',
                records: 0
            },
            {
                id: 4,
                name: 'bar.net',
                type: 'MASTER',
                records: 0
            }
        ], 'Result fail for ' + res.config.url);

        //Type filter
        var res = await req({
            url: '/domains?type=NATIVE',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            {
                id: 3,
                name: 'foo.de',
                type: 'NATIVE',
                records: 1
            }
        ], 'Result fail for ' + res.config.url);
    });

    await test('user', async function (assert, req) {
        //Type filter
        var res = await req({
            url: '/domains',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK for user');
        assert.equal(res.data.results, [
            {
                id: 1,
                name: 'example.com',
                type: 'MASTER',
                records: 3
            },
            {
                id: 2,
                name: 'slave.example.net',
                type: 'SLAVE',
                master: '12.34.56.78',
                records: 0
            }
        ], 'Result fail for user on ' + res.config.url);
    });
});