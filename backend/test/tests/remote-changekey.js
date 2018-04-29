const test = require('../testlib');

const NodeRSA = require('node-rsa');

const privkey =
    `-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQCrJ/UoQoN5rO1nwrWBNDr3TgPBkm6UmN/B6NY7RXcYTJOFEP6i
WqTj9Pw8aT8/DSn2uTMeQK6kWNUAWmRaylQI2QHQdPtrI6piTpjvKm+KbR+n3e4Q
J/zOcg06cHYJJiyhPjfC12j3ZxINOV3LDbEKq4s0HxMGYZHPu+UezapeeQIDAQAB
AoGAGGkbgwFxhPIP7gOMJYBQhKMA0CPVV6YyC5LsswlmQfXx+EGDP56T89sl+mu8
VH7JJGInk0IAZnow7tr1gylmMJ0ir6KfDKZQG95tkFHwCVM3ZqUx/X8VAVuZT2mo
6ckAC7/ZrqORiFCNDC1kWgiaNj7GldvcbNOGUIBOkStgM4ECQQDVLWI/hO0fiPhT
QWVu+4md1NjSv9MZdaIdm+FEVKyTjN/j1fDLNFIguC24veYvsgKf2AyYAJqiAihz
RQWey38RAkEAzYmjjZuKmtsaUknZxmYVJwZlatvHv/3V2REa3UwhVXhgpbBGahav
khH8W5u4JJ/VUpX34wje8g/Gp2M6aCg46QJAGtux8jDMM1ntd4fYwMfeSc1kWAEl
FqMUfsiB9Dr610g7eRgeU2vPISIzWIBMfRvfasYsqAYDdX/yGrvKfnxDEQJAcTUr
aXbPfAXMVKCqm3Vkly8VsyrEtcHZBItAUb156rq3+OrDjfFa2MihR8/YOAv1ElzZ
wSoEqiz4TQABjpcA6QJAX1QXYhHQpjLj4UF+8TkZg93Zmd86W5CN/gXSTFJGrZ8M
3DOyePDIw1omSzyfvYa3Rbl/NL5BxFH6cURg++z8FA==
-----END RSA PRIVATE KEY-----`;
const key = new NodeRSA(privkey, 'pkcs1', { signingScheme: 'pkcs1-sha512' });

test.run(async function () {
    await test('admin', async function (assert, req) {
        // Test updating
        var time = Math.floor(new Date() / 1000);

        var res = await req({
            url: '/remote/updatekey',
            method: 'post',
            data: {
                record: 1,
                content: 'foobarbaz',
                time: time,
                signature: key.sign('1foobarbaz' + time, 'base64')
            }
        });

        assert.equal(res.status, 204, 'Update should succeed');

        var res = await req({
            url: '/records/1',
            method: 'get'
        });

        assert.equal(res.data.content, 'foobarbaz', 'Updating should change content.');

        var res = await req({
            url: '/remote/updatekey',
            method: 'post',
            data: {
                record: 1,
                content: 'foobarbaz',
                time: time,
                signature: key.sign('1foobarbazdef' + time, 'base64')
            }
        });

        assert.equal(res.status, 403);

        // Test not existing record
        var res = await req({
            url: '/remote/updatekey',
            method: 'post',
            data: {
                record: 100,
                content: 'foobarbaz',
                time: time,
                signature: key.sign('1foobarbazdef' + time, 'base64')
            }
        });

        assert.equal(res.status, 404, 'Not existing record should trigger error');

        // Test missing fields
        var res = await req({
            url: '/remote/updatekey',
            method: 'post',
            data: {
                record: 100,
                signature: key.sign('1foobarbazdef' + time, 'base64')
            }
        });

        assert.equal(res.status, 422, 'Missing field should fail');

        // Test wrong time
        var time = Math.floor(new Date() / 1000) - 60;
        var res = await req({
            url: '/remote/updatekey',
            method: 'post',
            data: {
                record: 1,
                content: 'foobarbaz',
                time: time,
                signature: key.sign('1foobarbaz' + time, 'base64')
            }
        });

        assert.equal(res.status, 403, 'Wrong time should fail');
    });
});