<?php

namespace Rabble\DatatableBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DatatableProvider implements ExpressionFunctionProviderInterface, VariableProviderInterface
{
    private TranslatorInterface $translator;

    private RouterInterface $router;

    private Environment $twig;

    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * DatatableProvider constructor.
     */
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        Environment $twig,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->twig = $twig;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getVariables(): array
    {
        return [
            'Routing' => $this->router,
            'Templating' => $this->twig,
            'Translator' => $this->translator,
            'Authorization' => $this->authorizationChecker,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('trans', function ($id, $parameters = '[]', $domain = "'messages'") {
                return sprintf('$Translator->trans(%1$s, %2$s, %3$s)', $id, $parameters, $domain);
            }, function ($args, $id, $parameters = [], $domain = 'messages') {
                return $this->translator->trans($id, $parameters, $domain);
            }),
            new ExpressionFunction('is_granted', function ($attribute, $subject = 'null') {
                return sprintf('$Authorization->isGranted(%1$s, %2$s)', $attribute, $subject);
            }, function ($args, $attribute, $subject = null) {
                return $this->authorizationChecker->isGranted($attribute, $subject);
            }),
            new ExpressionFunction('dump', function ($object) {
                return sprintf('
                call_user_func(function($subject) {
                $cloner = new Symfony\\Component\\VarDumper\\Cloner\\VarCloner();
                $dumper = new Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper();
                return $dumper->dump($cloner->cloneVar($subject), true);
                }, %1$s);', $object);
            }, function ($args, $object) {
                $cloner = new VarCloner();
                $dumper = new HtmlDumper();

                return $dumper->dump($cloner->cloneVar($object), true);
            }),
        ];
    }
}
