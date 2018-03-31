const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test missing fields
        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'abc.de'
            }
        });

        assert.equal(res.status, 422, 'Missing type filed should trigger error.');

        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'abc.de',
                type: 'SLAVE'
            }
        });

        assert.equal(res.status, 422, 'Missing master field for SLAVE domain should trigger error.');

        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'abc.de',
                type: 'FOO'
            }
        });

        assert.equal(res.status, 400, 'Invalid domain type should trigger error.');

        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'foo.de',
                type: 'MASTER'
            }
        });

        assert.equal(res.status, 409, 'Existing domain should trigger error.');

        //Test creation of master zone
        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'master.de',
                type: 'MASTER'
            }
        });

        assert.equal(res.status, 201, 'Creation should be successfull');
        assert.equal(res.data, {
            id: 6,
            name: 'master.de',
            type: 'MASTER'
        }, 'Creation result fail.')

        //Test creation of native zone
        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'native.de',
                type: 'NATIVE'
            }
        });

        assert.equal(res.status, 201, 'Creation should be successfull');
        assert.equal(res.data, {
            id: 7,
            name: 'native.de',
            type: 'NATIVE'
        }, 'Creation result fail.')

        //Test creation of slave zone
        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'slave.de',
                type: 'SLAVE',
                master: '1.2.3.4'
            }
        });

        assert.equal(res.status, 201, 'Creation should be successfull');
        assert.equal(res.data, {
            id: 8,
            name: 'slave.de',
            type: 'SLAVE',
            master: '1.2.3.4'
        }, 'Creation result fail.')

        //Get master domain
        var res = await req({
            url: '/domains/6',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Domain access for master domain should be OK.');
        assert.equal(res.data, {
            id: 6,
            name: 'master.de',
            type: 'MASTER',
            records: 0
        }, 'Master domain data mismatch');

        //Get native domain
        var res = await req({
            url: '/domains/7',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Domain access for native domain should be OK.');
        assert.equal(res.data, {
            id: 7,
            name: 'native.de',
            type: 'NATIVE',
            records: 0
        }, 'Native domain data mismatch');

        //Get slave domain
        var res = await req({
            url: '/domains/8',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Domain access for slave domain should be OK.');
        assert.equal(res.data, {
            id: 8,
            name: 'slave.de',
            type: 'SLAVE',
            records: 0,
            master: '1.2.3.4'
        }, 'Slave domain data mismatch');

        //Update slave domain
        var res = await req({
            url: '/domains/8',
            method: 'put',
            data: {
                master: '9.8.7.6'
            }
        });

        assert.equal(res.status, 204, 'Slave update should return no content');

        //Check if update succeded
        var res = await req({
            url: '/domains/8',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Slave domain should be accessible after update.');
        assert.equal(res.data.master, '9.8.7.6', 'Slave update had no effect');

        //Check if update fails for non existing domain
        var res = await req({
            url: '/domains/100',
            method: 'put',
            data: {
                master: '9.8.7.6'
            }
        });

        assert.equal(res.status, 404, 'Update on not existing domain should fail.');

        //Check if update fails for master zone
        var res = await req({
            url: '/domains/1',
            method: 'put',
            data: {
                master: '9.8.7.6'
            }
        });

        assert.equal(res.status, 405, 'Update on master zone should fail.');

        //Check if update fails for missing field
        var res = await req({
            url: '/domains/100',
            method: 'put',
            data: {
                foo: 'bar'
            }
        });

        assert.equal(res.status, 422, 'Update with missing master field should fail.');

        //Delete not existing domain
        var res = await req({
            url: '/domains/100',
            method: 'delete'
        });

        assert.equal(res.status, 404, 'Non existing domain deletion should be 404.');

        //Delete existing domain
        var res = await req({
            url: '/domains/8',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of domain 8 should be successfull.');
    });

    await test('user', async function (assert, req) {
        //Test insufficient privileges for add
        var res = await req({
            url: '/domains',
            method: 'post',
            data: {
                name: 'foo.de'
            }
        });

        assert.equal(res.status, 403, 'Domain creation should be forbidden for users.')

        //Test insufficient privileges for delete
        var res = await req({
            url: '/domains/1',
            method: 'delete'
        });

        assert.equal(res.status, 403, 'Domain deletion should be forbidden for users.');

        //Test insufficient permissions
        var res = await req({
            url: '/domains/2',
            method: 'put',
            data: {
                master: '9.8.7.6'
            }
        });

        assert.equal(res.status, 403, 'Update of slave zone should be forbidden for non admins.');

        //Test insufficient privileges for get
        var res = await req({
            url: '/domains/3',
            method: 'get'
        });

        assert.equal(res.status, 403, 'Domain get for domain 3 should be forbidden.');

        //Test privileges for get
        var res = await req({
            url: '/domains/1',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Domain access for domain 1 should be OK.');
        assert.equal(res.data, {
            id: 1,
            name: 'example.com',
            type: 'MASTER',
            records: 3
        }, 'Domain 3 data mismatch');
    });
});