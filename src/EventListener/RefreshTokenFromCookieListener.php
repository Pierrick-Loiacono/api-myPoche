<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RefreshTokenFromCookieListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // Si la route correspond au rafraîchissement du token
        if ($request->attributes->get('_route') === 'gesdinet_jwt_refresh_token') {
            // Récupérer le Refresh Token depuis le cookie
            $refreshToken = $request->cookies->get('refresh_token');

            if ($refreshToken) {
                // Injecter le token dans le corps de la requête pour que Gesdinet le traite
                $request->request->set('refresh_token', $refreshToken);
            }
        }
    }
}
