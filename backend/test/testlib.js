const assert = require('assert');
const axios = require('axios');

async function runTest(f) {
    const assertObj = {
        equal: assert.deepStrictEqual,
        true: assert.ok
    };

    var requestObj = axios.create({
        baseURL: process.argv[2],
        validateStatus: function () { return true; }
    });

    try {
        const token = await logIn(assertObj, requestObj);

        requestObj = axios.create({
            baseURL: process.argv[2],
            validateStatus: function () { return true; },
            headers: { 'X-Authentication': token }
        });

        await f(assertObj, requestObj);

        await logOut(assertObj, requestObj, token);
    } catch (e) {
        if (e instanceof assert.AssertionError) {
            console.log(e.toString());
            console.log('\nExpected:');
            console.log(e.expected);
            console.log('\nGot:');
            console.log(e.actual);
            process.exit(2);
        } else {
            console.log(e.toString());
            process.exit(1);
        }
    }

    process.exit(0);
}

async function logIn(assert, req) {
    //Try to login with valid username and password
    var res = await req({
        url: '/sessions',
        method: 'post',
        data: {
            username: 'admin',
            password: 'admin'
        }
    });

    console.log(res);

    assert.equal(res.status, 201, 'LOGIN: Status not valid');
    assert.equal(res.data.username, 'admin', 'LOGIN: Username should be admin');
    assert.equal(res.data.token.length, 86, 'LOGIN: Token length fail');

    return res.data.token;
}

async function logOut(assert, req, token) {
    //Try to logout check if this works
    var res = await req({
        url: '/sessions/' + token,
        method: 'delete'
    });

    assert.equal(res.status, 204, 'LOGOUT: Answer should be successfull but empty');
}

module.exports = runTest;