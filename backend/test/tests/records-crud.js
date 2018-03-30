const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test missing fields
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: 'foo.abc.de',
                type: 'A'
            }
        });

        assert.equal(res.status, 422, 'Missing fields should trigger error.');

        //Test invalid record type
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: "dns.example.com",
                type: "FOOBARBAZ",
                content: "1.2.3.4",
                priority: 0,
                ttl: 86400,
                domain: 1
            }
        });

        assert.equal(res.status, 400, 'Invalid record type should trigger error.');

        //Test adding for slave zone
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: "dns.example.com",
                type: "A",
                content: "1.2.3.4",
                priority: 0,
                ttl: 86400,
                domain: 2
            }
        });

        assert.equal(res.status, 404, 'Adding record for slave should trigger error.');

        //Test adding for not existing zone
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: "dns.example.com",
                type: "A",
                content: "1.2.3.4",
                priority: 0,
                ttl: 86400,
                domain: 100
            }
        });

        assert.equal(res.status, 404, 'Adding record to not existing domain should trigger error.');

        //Test adding of record
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: 'dns.example.com',
                type: 'A',
                content: '1.2.3.4',
                priority: 0,
                ttl: 86400,
                domain: 1
            }
        });

        assert.equal(res.status, 201, 'Adding of record should succeed.');
        assert.equal(res.data, {
            id: 5,
            name: 'dns.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 86400,
            domain: 1
        }, 'Adding record return data fail.');

        //Get not existing record
        var res = await req({
            url: '/records/100',
            method: 'get'
        });

        assert.equal(res.status, 404, 'Get of not existing record should fail.');

        //Get created record
        var res = await req({
            url: '/records/5',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Get of created record should succeed.');
        assert.equal(res.data, {
            id: 5,
            name: 'dns.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 86400,
            domain: 1
        }, 'Record data should be the same it was created with.');


        //Update record
        var res = await req({
            url: '/records/5',
            method: 'put',
            data: {
                name: 'foo.example.com'
            }
        });

        assert.equal(res.status, 204, 'Updating record should succeed');

        //Get updated record
        var res = await req({
            url: '/records/5',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Get updated record should succeed.');
        assert.equal(res.data, {
            id: 5,
            name: 'foo.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 86400,
            domain: 1
        }, 'Updated record has wrong data.');

        //Delete not existing record
        var res = await req({
            url: '/records/100',
            method: 'delete'
        });

        assert.equal(res.status, 404, 'Deletion of not existing record should fail.');

        //Delete existing record
        var res = await req({
            url: '/records/5',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of existing record should succeed.');

    });

    await test('user', async function (assert, req) {
        //Test insufficient privileges for add
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: 'dns.example.com',
                type: 'A',
                content: '1.2.3.4',
                priority: 0,
                ttl: 86400,
                domain: 3
            }
        });

        assert.equal(res.status, 403, 'Adding of record should fail for user.');

        //Test insufficient privileges for delete
        var res = await req({
            url: '/records/4',
            method: 'delete'
        });

        assert.equal(res.status, 403, 'Deletion of record should fail for user.');

        //Test insufficient privileges for update
        var res = await req({
            url: '/records/4',
            method: 'put',
            data: {
                name: 'foo.example.com',
                ttl: 60
            }
        });

        assert.equal(res.status, 403, 'Updating record should succeed');

        //Test adding of record
        var res = await req({
            url: '/records',
            method: 'post',
            data: {
                name: 'dns.example.com',
                type: 'A',
                content: '1.2.3.4',
                priority: 0,
                ttl: 86400,
                domain: 1
            }
        });

        assert.equal(res.status, 201, 'Adding of record should succeed.');
        assert.equal(res.data, {
            id: 6,
            name: 'dns.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 86400,
            domain: 1
        }, 'Adding record return data fail.');

        //Get created record
        var res = await req({
            url: '/records/6',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Get of created record should succeed.');
        assert.equal(res.data, {
            id: 6,
            name: 'dns.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 86400,
            domain: 1
        }, 'Record data should be the same it was created with.');


        //Update record
        var res = await req({
            url: '/records/6',
            method: 'put',
            data: {
                name: 'foo.example.com',
                ttl: 60
            }
        });

        assert.equal(res.status, 204, 'Updating record should succeed');

        //Get updated record
        var res = await req({
            url: '/records/6',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Get updated record should succeed.');
        assert.equal(res.data, {
            id: 6,
            name: 'foo.example.com',
            type: 'A',
            content: '1.2.3.4',
            priority: 0,
            ttl: 60,
            domain: 1
        }, 'Updated record has wrong data.');

        //Delete existing record
        var res = await req({
            url: '/records/6',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of existing record should succeed.');
    });
});