<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create 4 clients
        for ($i = 0; $i < 3; ++$i) {
            $client = new Client();
            $client->setName('Company'.$i);
            $client->setEmail('company'.$i.'@company.com');
            $client->setPassword($this->passwordHasher->hashPassword($client, 'password'));
            $manager->persist($client);
            $this->addReference('client'.$i, $client);
        }

        // creates 50 customers
        for ($i = 0; $i < 50; ++$i) {
            $customer = new Customer();
            $customer->setFirstName('Client'.$i);
            $customer->setLastName($i);
            $customer->setEmail('client'.$i.'@final.com');
            $client = $this->getReference('client'.rand(0, 2));
            $customer->setClient($client);
            $manager->persist($customer);
        }

        // create 100 products
        for ($i = 0; $i < 100; ++$i) {
            $price = rand(8895, 199999) / 100;
            $product = new Product();
            $product->setName('ProductName'.$i);
            $product->setDescription('ProductDescription'.$i);
            $product->setPrice($price);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
