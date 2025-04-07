<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PushSubscriptionController extends AbstractController
{
    #[Route('/api/push-subscribe', methods: ['POST'], format: 'json')]
    public function subscribe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['endpoint'], $data['keys']['auth'], $data['keys']['p256dh'])) {
            return $this->json(['error' => 'Invalid subscription data'], 400);
        }

        $employee = $this->getUser();
        if (!$employee instanceof Employee) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = new PushSubscription();
        $subscription->setEndpoint($data['endpoint']);
        $subscription->setAuthToken($data['keys']['auth']);
        $subscription->setP256dhKey($data['keys']['p256dh']);
        $subscription->setEmployee($employee);
        $em->persist($subscription);
        $em->flush();

        return $this->json(['status' => 'Subscribed']);
    }
}
