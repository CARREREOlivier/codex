<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Enum\ArticleStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $slugger = new AsciiSlugger();

        for ($i = 1; $i <= 6; $i++) {
            $article = new Article();
            $title = "Article publié n°$i";
            $article->setTitle($title);
            $article->setSlug(strtolower($slugger->slug($title)));
            $article->setContent("Contenu de l’article publié n°$i. Lorem ipsum dolor sit amet...");
            $article->setStatus(ArticleStatus::PUBLIE);
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($article);
        }

        // Article brouillon
        $article = new Article();
        $title = "Article brouillon";
        $article->setTitle($title);
        $article->setSlug(strtolower($slugger->slug($title)));
        $article->setContent("Ceci est un article brouillon. Il ne devrait pas s’afficher publiquement.");
        $article->setStatus(ArticleStatus::BROUILLON);
        $article->setCreatedAt(new \DateTimeImmutable());
        $article->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($article);

        $manager->flush();
    }
}
