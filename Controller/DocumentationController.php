<?php
/**
 * @author Sergey Hashimov <hashimov.sergey@gmail.com>
 */

namespace Gns\GnsFilterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends AbstractController
{
    /**
     * @Route("/data-filter")
     */
    public function filtration(): Response
    {
        return $this->render('@GnsFilter/Documentation/filtration.html.twig');
    }
}