const cartesianProduct = require('cartesian-product');

(async function () {
    await require('../testlib')('admin', async function (assert, req) {
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

        //Delete not existing domain
        var res = await req({
            url: '/domains/100',
            method: 'delete'
        });

        assert.equal(res.status, 404, 'Non existing domain deletion should be 404.');

        //Delete existing domain
        var res = await req({
            url: '/domains/1',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of domain 1 should be successfull.');
    });

    await require('../testlib')('user', async function (assert, req) {
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
    });

})();