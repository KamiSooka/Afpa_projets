<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    // Méthode de contrôleur pour afficher la liste des recettes
    #[Route('/recettes', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $repository): Response
    {
        // Récupération des recettes avec une durée inférieure à 20 minutes
        $recipes = $repository->findWithDurationLowerThan(20);
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    // Méthode de contrôleur pour afficher une recette individuelle
    #[Route('/recettes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $recipeRepository): Response
    {
        // Récupération de la recette selon son identifiant
        $recipe = $recipeRepository->find($id);

        // Vérification du slug et redirection si nécessaire
        if ($recipe->getSlug() !== $slug) {
            return $this->redirectToRoute('recipe.show', [
                'slug' => $recipe->getSlug(),
                'id' => $recipe->getId()
            ]);
        }

        // Affichage de la recette
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    // #[Route('/recettes/{id}/edit', name: 'recipe.edit')]
    // public function edit(Recipe $recipe, Request $request): Response
    // {
    //     $form = $this->createForm(RecipeType::class, $recipe);
    //     $form->handleRequest($request);
    //     dd($recipe);

    //     return $this->render('recipe/edit.html.twig', [
    //         'recipe' => $recipe,
    //         'form' => $form->createView()
    //     ]);
    // }
    //
    // Test d'un autre code : 

    // Méthode de contrôleur pour modifier une recette
    #[Route('/recettes/{id}/edit', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request,  EntityManagerInterface $em): Response
    {
        // Création du formulaire de modification de recette
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        // dd($recipe);

        // Traitement du formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Persistez les changements dans la base de données
            // $recipe->setUpdateAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'La recette a bien été modifiée !');
            return $this->redirectToRoute('recipe.index');
        }

        // Affichage du formulaire de modification
        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

    // Méthode de contrôleur pour créer une nouvelle recette
    #[Route('/recettes/create', name: 'recipe.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // Création d'une nouvelle instance de recette et du formulaire associé
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        // Traitement du formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {

            // Persistez la nouvelle recette dans la base de données
            // $recipe->setCreateAt(new \DateTimeImmutable());
            // $recipe->setUpdateAt(new \DateTimeImmutable());
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a bien été ajoutée !');
            return $this->redirectToRoute('recipe.index');
        }
        // Affichage du formulaire de creation d'une nouvelle recette
        return $this->render('recipe/create.html.twig', [
            'form' => $form->createView()
        ]);
    }


    // Methode de contrôleur pour supprimer une recette
    #[Route('/recettes/{id}/edit', name: 'recipe.delete', methods: ['DELETE'])]
    public function remove(Recipe $recipe, EntityManagerInterface $em)
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'La recette a bien été supprimée !');
        return $this->redirectToRoute('recipe.index');
    }
}
