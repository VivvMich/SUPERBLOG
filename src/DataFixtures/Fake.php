<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Blog;



class Fake extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faketitle = file_get_contents("https://baconipsum.com/api/?type=meat-and-filler&sentences=40");
        $titles = json_decode($faketitle, true);
        $titleArray = explode(".", $titles[0]);
        //resultat d'une API dans le content//
        $fakeContent = file_get_contents("https://baconipsum.com/api/?type=all-meat&paras=40start-with-lorem=1");
        $contents = json_decode($fakeContent, true);



        for($i = 0; $i < 40 ; $i += 2){
            $blog = new Blog();
            $blog->setTitle($titleArray[floor($i / 2)]);
            $blog->setContent($contents[$i] . "\n" . $contents[$i + 1] );


            //nous générons une date aléatoire entre le 1 janvier 2022 et le 1 fevrier 2023

            $dateDiff = 1675213200 - 1640998800;
            $fakeDate = 1640998800 + rand(1, $dateDiff);
            $date = date('Y/m/d H:i:s', $fakeDate);
            $modifyDate = new \DateTimeImmutable();
            $blog->setCreateAt($modifyDate->modify($date));

            //ajout d'un utilisateur aléatoire

            $ur = $manager->getRepository(User::class);
            $users = $ur->findAll();
            //$usersCount = count($users);
            $selectUser = $users[0];
            $blog->setUser($selectUser);

            $br = $manager->getRepository(Blog::class);
            $br->save($blog, true);
        }
    }
}