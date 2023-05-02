<?php

namespace App\Controller;

use App\Entity\FunctionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MathController extends AbstractController
{
    #[Route('/math/func', name: 'app_math_func', methods: ["POST"])]
    public function func(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $expressionLanguage = new ExpressionLanguage();

        $functionCalculator = new FunctionCalculator(
            $data->law, $expressionLanguage);
        $dom = $data->dom;
        $image = $functionCalculator->calculate($dom);

        return new JsonResponse([
            'law' => $functionCalculator->getLaw(),
            'dom' => $dom,
            'image' => $image
        ], 200);
    }
}
