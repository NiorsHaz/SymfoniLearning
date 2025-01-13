<?php

namespace App\EventListener;

use App\Service\JwtTokenManager;
use Psr\Log\LoggerInterface;
use App\Annotation\TokenRequired;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationReader;

class TokenListener
{
    private $jwtTokenManager;
    private $reader;
    private $logger;

    public function __construct(JwtTokenManager $jwtTokenManager, LoggerInterface $logger)
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->reader = new AnnotationReader(); // Initialisez le reader ici
        $this->logger = $logger; // Inject LoggerInterface
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);
        $this->logger->info('TokenListener: Executing controller method: ' . $method);

        // Vérifiez si l'attribut TokenRequired est présent
        $attributes = $method->getAttributes(TokenRequired::class);

        if (count($attributes) > 0) {
            // Récupérer la requête
            $request = $event->getRequest();

            // Extraire et valider le token
            $tokenString = $this->jwtTokenManager->extractTokenFromRequest($request);
            
            if ($method) {
                
                $request = $event->getRequest();
                $authorizationHeader = $this->jwtTokenManager->parseToken($request->headers->get('Authorization'));
                
                if (!$authorizationHeader || !$this->jwtTokenManager->validateToken($authorizationHeader)) {
                    return new JsonResponse(['error' => 'Token is not Valid'], Response::HTTP_UNAUTHORIZED);
                }

                if(!$tokenString || $tokenString === ''){
                    $event->setController(function () {
                        return new JsonResponse(['error' => 'Token is Empty'], Response::HTTP_UNAUTHORIZED);
                    });   
                }
                else{
                    $parsedToken = $this->jwtTokenManager->parseToken($tokenString);

                    if (!$parsedToken || !$this->jwtTokenManager->validateToken($parsedToken)) {
                        // Si le token est invalide, retournez une réponse 401
                        $event->setController(function () {
                            return new JsonResponse(['error' => 'Invalid or expired token'], Response::HTTP_UNAUTHORIZED);
                        });
                    }
                }
            }
        }
    }
}
