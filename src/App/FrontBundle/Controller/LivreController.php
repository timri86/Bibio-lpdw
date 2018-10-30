<?php

namespace App\FrontBundle\Controller;

use App\FrontBundle\Entity\Livre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Livre controller.
 *
 */
class LivreController extends Controller
{
    /**
     * Lists all livre entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $livres = $em->getRepository('AppFrontBundle:Livre')->findAll();

        return $this->render('livre/index.html.twig', array(
            'livres' => $livres,
        ));
    }

    public function emprunterAction(Livre $livre, Request $request)
    {
        $user=$this->getUser();
        $users=$livre->getUsers();
        $test=false;
        $session = $request->getSession();
        foreach ($users as $user1 ){
            if ($user1==$user){
                $test=true;
            }
        }
        if ($test==true){
            $session->getFlashBag()->add('info', 'Vous avez déjà emprunter un exemplaire de ce livre');
            return $this->redirectToRoute('livre_index');
        }else{
            $nbexemplaire=$livre->getNbexemplaire()-1;
            $livre->addUser($user);
            $livre->setNbexemplaire($nbexemplaire);
            $em = $this->getDoctrine()->getManager();
            $em->persist($livre);
            $em->flush();
            $session->getFlashBag()->add('info', 'Action réalisée avec succès');
        }
        return $this->redirectToRoute('livre_index');
    }

    public function retourAction(Livre $livre)
    {
        $user=$this->getUser();
        $nbexemplaire=$livre->getNbexemplaire()+1;
        $livre->removeUser($user);
        $livre->setNbexemplaire($nbexemplaire);

        $em = $this->getDoctrine()->getManager();
        $em->persist($livre);
        $em->flush();

        return $this->redirectToRoute('livre_index');
    }

    public function mesempruntsAction()
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $livres = $em->getRepository('AppFrontBundle:Livre')->findAll();
        return $this->render('livre/mesemprunts.html.twig', array(
            'livres' => $livres,
        ));
    }
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listeempruntAction()
    {
        $em = $this->getDoctrine()->getManager();
        $livres = $em->getRepository('AppFrontBundle:Livre')->findAll();
        return $this->render('livre/listeemprunts.html.twig', array(
            'livres' => $livres,
        ));
    }
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $livre = new Livre();
        $form = $this->createForm('App\FrontBundle\Form\LivreType', $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($livre);
            $em->flush();

            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/new.html.twig', array(
            'livre' => $livre,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a livre entity.
     *
     */
    public function showAction(Livre $livre)
    {
        $deleteForm = $this->createDeleteForm($livre);

        return $this->render('livre/show.html.twig', array(
            'livre' => $livre,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Livre $livre)
    {
        $deleteForm = $this->createDeleteForm($livre);
        $editForm = $this->createForm('App\FrontBundle\Form\LivreType', $livre);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/edit.html.twig', array(
            'livre' => $livre,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Livre $livre)
    {
        $form = $this->createDeleteForm($livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($livre);
            $em->flush();
        }

        return $this->redirectToRoute('livre_index');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    private function createDeleteForm(Livre $livre)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('livre_delete', array('id' => $livre->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
