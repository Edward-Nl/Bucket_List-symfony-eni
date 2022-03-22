<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use App\Util\Censurator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    /**
     * @Route("/wishes", name="wish_list")
     */
    public function list(WishRepository $wishRepository): Response
    {
        $wishes = $wishRepository->findPublishedWishesWithCategories();
        return $this->render('wish/list.html.twig', [
            "wishes" => $wishes
        ]);
    }

    /**
     * @Route("/wishes/detail/{id}", name="wish_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, WishRepository $wishRepository): Response
    {
        $wish = $wishRepository->find($id);

        //Si il n'hexiste pas on lance une erreur 404
        if (!$wish){
            throw $this->createNotFoundException("This wish do not exist! Sorry!");
        }

        return $this->render('wish/detail.html.twig', [
            "wish" => $wish
        ]);
    }

    /**
     * @Route("/wishes/create", name="wish_create")
     * @throws \Exception
     */
    public function create(Request $request, EntityManagerInterface $entityManager, Censurator $censurator): Response{
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);
        $currentUserUsername = $this->getUser()->getUserIdentifier();
        $wish->setAuthor($currentUserUsername);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $wish->setDescription($censurator->censureText($wish->getDescription()));

            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash("success", "Idea successfully added");

            return $this->redirectToRoute('wish_detail', ['id'=>$wish->getId()]);
        }

        return $this->render('wish/create.html.twig', [
            'wishForm' => $wishForm->createView()
        ]);
    }

    /**
     * @Route("/wishes/delete/{id}", name="wish_delete")
     */
    public function delete(Wish $wish, EntityManagerInterface $entityManager){
        $entityManager->remove($wish);
        $entityManager->flush();
        $this->addFlash('success', 'Wish delete !');
        return $this->redirectToRoute('wish_list');
    }
}
