const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test query
        var res = await req({
            url: '/records/1/credentials',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Status should be OK');
        assert.equal(res.data.results, [
            {
                id: 1,
                description: 'Password Test',
                type: 'password'
            },
            {
                id: 3,
                description: 'Key Test 2',
                type: 'key'
            }
        ], 'Result fail for ' + res.config.url);
    });
});