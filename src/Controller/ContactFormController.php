<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\ContactType;
use App\Mail\MailerInterface;
use App\Model\Contact;
use App\ValueObject\Email;
use Exception;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactFormController extends Controller
{
    use LoggerAwareTrait;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \App\Mail\MailerInterface */
    private $mailer;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var string */
    private $sender;

    /** @var array */
    private $recipients;

    public function __construct(
        RouterInterface $router,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        string $sender,
        array $recipients
    ) {
        $this->router = $router;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->sender = $sender;
        $this->recipients = $recipients;
    }

    /**
     * Displays contact form and sends e-mail message when using POST request.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showContactFormAction(View $view, Request $request)
    {
        $form = $this->createForm(ContactType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $this->mailer->send(
                        $this->createEmail($form->getData())
                    );

                    // redirects user to confirmation page after successful sending of e-mail
                    return new RedirectResponse(
                        $this->router->generate('app.contact.submitted')
                    );
                } catch (Exception $e) {
                    $this->logger->error(
                        $e->getMessage()
                    );
                }
            }
        }

        $view->addParameters([
            'form' => $form->createView(),
        ]);

        return $view;
    }

    /**
     * Displays confirmation page after successful contact form submission.
     *
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submittedAction($template)
    {
        return $this->render($template, []);
    }

    /**
     * @param \App\Model\Contact $contact
     *
     * @return \App\ValueObject\Email
     */
    private function createEmail(Contact $contact): Email
    {
        return $this->mailer->create(
            $this->translator->trans('You have a new message from %from%', ['%from%' => $contact->getFrom()]),
            $this->renderView('@ezdesign/mail/contact.html.twig', [
                'contact' => $contact,
            ]),
            $this->sender,
            $this->recipients
        );
    }
}
