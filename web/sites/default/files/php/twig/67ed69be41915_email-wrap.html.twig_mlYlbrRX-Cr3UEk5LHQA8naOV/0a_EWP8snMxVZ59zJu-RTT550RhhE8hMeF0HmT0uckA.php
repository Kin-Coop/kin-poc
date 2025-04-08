<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/contrib/symfony_mailer/templates/email-wrap.html.twig */
class __TwigTemplate_0dccb255c094835f4127aa326b88482a extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 20
        $context["classes"] = [("email-type-" . \Drupal\Component\Utility\Html::getClass(        // line 21
($context["type"] ?? null))), ("email-sub-type-" . \Drupal\Component\Utility\Html::getClass(        // line 22
($context["sub_type"] ?? null)))];
        // line 25
        yield "
";
        // line 26
        if (($context["is_html"] ?? null)) {
            // line 27
            yield "<html>
<body>
<div";
            // line 29
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 29), "html", null, true);
            yield ">
  <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
    <tr>
      <td>
        <div style=\"padding: 0px 0px 0px 0px;\" class=\"clearfix\">
          ";
            // line 34
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["body"] ?? null), "html", null, true);
            yield "
        </div>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
";
        } else {
            // line 43
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["body"] ?? null), "html", null, true);
            yield "
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["type", "sub_type", "is_html", "attributes", "body"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/symfony_mailer/templates/email-wrap.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  77 => 43,  65 => 34,  57 => 29,  53 => 27,  51 => 26,  48 => 25,  46 => 22,  45 => 21,  44 => 20,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/symfony_mailer/templates/email-wrap.html.twig", "/home/kin/sites/kin/dir/web/modules/contrib/symfony_mailer/templates/email-wrap.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 20, "if" => 26];
        static $filters = ["clean_class" => 21, "escape" => 29];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['clean_class', 'escape'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
