const test = require('../testlib');
const cartesianProduct = require('cartesian-product');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test sorting in all combinations
        const sortCombinations = cartesianProduct([
            ['', 'id-asc', 'id-desc'],
            ['', 'name-asc', 'name-desc'],
            ['', 'type-asc', 'type-desc'],
            ['', 'content-asc', 'content-desc'],
            ['', 'priority-asc', 'priority-desc'],
            ['', 'ttl-asc', 'ttl-desc'],
        ]);

        for (list of sortCombinations) {
            list = list.filter((str) => str.length > 0);
            var sortQuery = list.join(',');

            var res = await req({
                url: '/records?sort=' + sortQuery,
                method: 'get'
            });

            assert.equal(res.status, 200);

            console.log(res.data);

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
            url: '/records?pagesize=2',
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
            url: '/records?pagesize=2&page=2',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.paging, {
            page: 2,
            total: 2,
            pagesize: 2
        }, 'Paging data fail for ' + res.config.url);
        assert.equal(res.data.results.length, 2, "Should be 2 results.");

        //Test query name
        var res = await req({
            url: '/records?queryName=foo&sort=id-asc',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [{
            id: 3,
            name: 'foo.example.com',
            type: 'AAAA',
            content: '::1',
            priority: 0,
            ttl: 86400,
            domain: 1
        },
        {
            id: 4,
            name: 'foo.de',
            type: 'A',
            content: '9.8.7.6',
            priority: 0,
            ttl: 86400,
            domain: 3
        }], 'Result fail for ' + res.config.url);

        //Type filter
        var res = await req({
            url: '/records?type=TXT,AAAA',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [{
            id: 2,
            name: 'sdfdf.example.com',
            type: 'TXT',
            content: 'foo bar baz',
            priority: 10,
            ttl: 60,
            domain: 1
        },
        {
            id: 3,
            name: 'foo.example.com',
            type: 'AAAA',
            content: '::1',
            priority: 0,
            ttl: 86400,
            domain: 1
        }], 'Result fail for ' + res.config.url);

        //Test query content
        var res = await req({
            url: '/records?queryContent=6&sort=id-asc',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [{
            id: 1,
            name: 'test.example.com',
            type: 'A',
            content: '12.34.56.78',
            priority: 0,
            ttl: 86400,
            domain: 1
        },
        {
            id: 4,
            name: 'foo.de',
            type: 'A',
            content: '9.8.7.6',
            priority: 0,
            ttl: 86400,
            domain: 3
        }], 'Result fail for ' + res.config.url);
    });
});