<?php

namespace App\Controller;

use App\Dto\PostCategoryDto;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    private SerializerInterface $serializer;
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private CategoryRepository $categoryRepository;

    /**
     * @param SerializerInterface $serializer
     * @param PostRepository $postRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(SerializerInterface $serializer, PostRepository $postRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator, CategoryRepository $categoryRepository)
    {
        $this->serializer = $serializer;
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->categoryRepository = $categoryRepository;
    }


    #[Route('/api/posts', name: 'api_getPosts' ,methods: ['GET'])]
    public function getPosts(): Response
    {
        // rechercher les posts dans la base de données
        $posts = $this->postRepository->findAll();
        //normalizer le tableau $posts
        // ->tranformer $post en un tableu associatif
        //$postsArray =$normalizer->normalize($posts);
        // Encoder en JSON
        //$postsJson= json_encode($postsArray);
        //
        //Serialiser le tableau $post en json
        $postsJson =$this->serializer->serialize($posts,'json',
                    ['groups' => 'list_posts']);

        //generer la response http
        /*
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('content-type','application/json');
        $response->setContent($postsJson);
        return $response ;
        */
        return new Response($postsJson,200,['content-type'=>'application/json']);
    }
    #[Route('/api/posts/{id}', name: 'api_getPostByID' ,methods: ['GET'])]
    public function getPostByID($id): Response
    {
        $post = $this->postRepository->find($id);
        //generer une erreur si le poste rechercher n'existe pas
        if(!$post){
            return $this->generateError(Response::HTTP_NOT_FOUND,"le post n'existe pas");
        }

        $postJson =$this->serializer->serialize($post,'json',
            ['groups' => 'get_post']);
        return new Response($postJson,200,['content-type'=>'application/json']);
    }

    #[Route('/api/posts', name: 'api_createPost' ,methods: ['POST'])]
    public function createPost (Request $request) :Response{
        //Recuperer dans la requete le body contenant le json du nouveau post
        $bodyRequest = $request->getContent();

        //deserializer le json en un objet post
        try {
            //Surveillez si le code ci dessous leve une exception
            $post = $this->serializer->deserialize($bodyRequest,PostCategoryDto::class,'json');

        }
        catch (NotEncodableValueException $exception) {
            return $this->generateError(Response::HTTP_BAD_REQUEST , $exception->getMessage());

        }


        //VALIDATION DES DONNEES EN FONCTION DES REGLES DE VALIDATION DEFINIE
        $erreur=$this->validator->validate($post);
        //tester si il ya des erreur
        if(count($erreur)> 0){
            //transforme le tableau en json
            $erreurJson = $this->serializer->serialize($erreur , 'json');
            return new Response($erreurJson,Response::HTTP_BAD_REQUEST,['content-type'=>'application/json']);
        }

        //Inserer le nouveau post dans la db
        $post->setCreatedAt(new \DateTime());
        $this->entityManager->persist($post); // Creer INSERT
        $this->entityManager->flush();
        //Serializer $post en json
        $postJson = $this->serializer->serialize($post ,'json');
        return new Response($postJson,Response::HTTP_CREATED,['content-type'=>'application/json']);

    }

    #[Route('/api/posts/{id}', name: 'api_deletePostByID' ,methods: ['DELETE'])]
    public function deletePostByID($id): Response{
        $post = $this->postRepository->find($id);
        if(!$post){
            return $this->generateError(Response::HTTP_NOT_FOUND,"le post a supprimer n'existe pas");
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();
        return new Response(null,Response::HTTP_NO_CONTENT,['content-type'=>'application/json']);
    }

    #[Route('/api/posts/{id}', name: 'api_updatePostByID' ,methods: ['PUT'])]
    public function updatePostById($id, Request $request): Response{
        //recuper le body de la request 1er chose
        $bodyRequest = $request->getContent();
        //recup le post
        $post = $this->postRepository->find($id);
        if(!$post){
            return $this->generateError(Response::HTTP_NOT_FOUND,"le post a modifer n'existe pas");
        }
        try {
            //Surveillez si le code ci dessous leve une exception
            $this->serializer->deserialize( $bodyRequest,Post::class,'json',['object_to_populate' => $post]);
        }
        catch (NotEncodableValueException $exception) {
            return $this->generateError(Response::HTTP_BAD_REQUEST , $exception->getMessage());

        }

        //modifier le post avec les données du body (json)

        //modifier le post dans la base de données
        $this->entityManager->flush();
        return new Response(null , Response::HTTP_NO_CONTENT) ;
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

}
