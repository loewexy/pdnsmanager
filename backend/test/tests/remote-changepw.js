const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        // Test updating
        var res = await req({
            url: '/remote/updatepw?record=1&content=foobarbaz&password=test',
            method: 'get'
        });

        assert.equal(res.status, 204);

        var res = await req({
            url: '/records/1',
            method: 'get'
        });

        assert.equal(res.data.content, 'foobarbaz', 'Updating should change content.');

        // Test updating with invalid password
        var res = await req({
            url: '/remote/updatepw?record=1&content=foobarbaz&password=foo',
            method: 'get'
        });

        assert.equal(res.status, 403);

        // Test updating non existing record
        var res = await req({
            url: '/remote/updatepw?record=100&content=foobarbaz&password=foo',
            method: 'get'
        });

        assert.equal(res.status, 404);
    });
});