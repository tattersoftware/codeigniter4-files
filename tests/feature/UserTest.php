<?php

use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class UserTest extends FeatureTestCase
{
    public function testShowsOwnFiles()
    {
        [$user, $file] = $this->createUserWithFile();
        service('auth')->login($user);

        $result = $this->get('files/user/' . $user->id);
        $result->assertSee('Manage My Files', 'h1');
        $result->assertSee($file->filename);
    }

    public function testShowsOtherFiles()
    {
        [$user, $file] = $this->createUserWithFile();
        service('auth')->login($user);

        $result = $this->get('files/user/1000');
        $result->assertSee('Browse User Files', 'h1');
        $result->assertDontSee($file->filename);
    }
}
