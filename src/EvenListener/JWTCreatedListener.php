<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onLexikJwtAuthenticationOnJwtCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        // Verificar si el usuario está verificado
        if (!$user->isVerified()) {
            throw new \Exception('Debes verificar tu email antes de iniciar sesión.');
        }

        // Añadir custom claims al token JWT
        $payload = $event->getData();
        $payload['isVerified'] = $user->isVerified(); // Añadir el estado de verificación al JWT
        $event->setData($payload);
    }

    public function TokenIdMetido(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        // Agregamos el ID del usuario al payload del token
        $payload = $event->getData();
        $payload['id'] = $user->getId();

        $event->setData($payload);
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        // Verifica si se obtiene el rol correctamente
        error_log('Roles obtenidos en onJWTCreated: ' . json_encode($user->getRoles()));

        $roles = $user->getRoles();
        $payload = $event->getData();
        $payload['rol'] = $roles[0] ?? 'User'; // Asegura que siempre haya un rol

        // Log para ver el payload final antes de enviarlo
        error_log('Payload final del JWT: ' . json_encode($payload));

        $event->setData($payload);
    }



}