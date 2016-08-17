<?php
require_once 'Intercom.php';

class IntercomTest extends PHPUnit_Framework_TestCase
{
    public function testGetAllUsers()
    {
        $intercom = new Intercom('dummy-app-id', 'dummy-api-key');

        $users = $intercom->getAllUsers(1, 1);

        // Retry if failing on the first attempt.
        if (!is_object($users)) {
            $users = $intercom->getAllUsers(1, 1);
        }

        $lastError = $intercom->getLastError();

        $this->assertTrue(is_object($users), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('users', $users);
    }

    /**
     * @group Travis
     */
    public function testCreateUser()
    {
        $intercom = new Intercom('dummy-app-id', 'dummy-api-key');

        $userId = 'userId001';
        $email = 'email@example.com';
        $res = $intercom->createUser('userId001', $email);
        $lastError = $intercom->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertEquals($email, $res->email);
        $this->assertObjectHasAttribute('user_id', $res);
        $this->assertEquals($userId, $res->user_id);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetUser()
    {
        $intercom = new Intercom('dummy-app-id', 'dummy-api-key');

        $res = $intercom->getUser('userId001');
        $lastError = $intercom->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertObjectHasAttribute('user_id', $res);
    }

    /**
     * @group Travis
     */
    public function testUpdateUser()
    {
        $intercom = new Intercom('dummy-app-id', 'dummy-api-key');

        $userId = 'userId001';
        $email = 'new+email@example.com';
        $res = $intercom->updateUser('userId001', $email);
        $lastError = $intercom->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertEquals($email, $res->email);
        $this->assertObjectHasAttribute('user_id', $res);
        $this->assertEquals($userId, $res->user_id);
    }

    /**
     * @group Travis
     */
    public function testCreateImpression()
    {
        $intercom = new Intercom('dummy-app-id', 'dummy-api-key');

        $res = $intercom->createImpression('userId001');
        $lastError = $intercom->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('unread_messages', $res);
    }
}
?>