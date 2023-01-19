<?php

namespace App\Controller;

use App\Dto\CategoryCountPostDto;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;
    private SerializerInterface $serializer;
    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository,SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/api/category', name: 'app_getCategory')]
    public function getCategory(): Response
    {
        $Cate = $this->categoryRepository->findAll();

        $CateJson =$this->serializer->serialize($Cate,'json',
            ['groups' => 'list_category']);


        return new Response($CateJson,200,['content-type'=>'application/json']);
    }

    #[Route('/api/category/{id}/posts' , name:'app_postByCategory')]
    public function postsByCategory($id):Response{
        $category = $this->categoryRepository->find($id);
        if(!$category){
            return $this->generateError(Response::HTTP_NOT_FOUND,"la categories n'a pas de post");
        }
        $post = $category->getPosts();
        $postJson = $this->serializer->serialize($post,'json',["groups"=>"get_category"]);
        return new Response($postJson,200,['content-type'=>'application/json']);
    }

    private function generateError(int $status,string $message) : Response{
        //creer un tableau associatif correspondant a l'erreur
        $erreur = [
            'status' => $status ,
            'message' => $message
        ];
        //renvoyer la reponse en json
        return new Response(json_encode($erreur),$status,["content-type"=>"application/json"]);
    }

    #[Route('/api/category/{id}' , name:'app_getCategoryById')]
    public function getCategoryById($id):Response{
        $category = $this->categoryRepository->find($id);
        if(!$category){
            return $this->generateError(Response::HTTP_NOT_FOUND,"la categories n'existe pas");
        }
        $categoryDto = new CategoryCountPostDto();
        $categoryDto ->setTitle($category->getTitle());
        $categoryDto ->setId( $category->getId());
        $categoryDto ->setNbPosts(count($category->getPosts()));
        $categoryDtoJson =  $this->serializer->serialize($categoryDto,'json');
        return new Response($categoryDtoJson,200,['content-type'=>'application/json']);
    }
}
