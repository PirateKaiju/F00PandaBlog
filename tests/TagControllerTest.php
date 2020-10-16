<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{

    private $tagRoot = "/tag/";

    public function testCreateSuccess()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('testuser@test.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', $this->tagRoot."create");

        $this->assertResponseIsSuccessful();
        //$this->assertSelectorTextContains('h1', 'Hello World');
    }

    public function testEditSuccess()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('testuser@test.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', $this->tagRoot."1/edit");//TODO: MAKE THIS MORE FLEXIBLE

        $this->assertResponseIsSuccessful();
        //$this->assertSelectorTextContains('h1', 'Hello World');
    }
}
