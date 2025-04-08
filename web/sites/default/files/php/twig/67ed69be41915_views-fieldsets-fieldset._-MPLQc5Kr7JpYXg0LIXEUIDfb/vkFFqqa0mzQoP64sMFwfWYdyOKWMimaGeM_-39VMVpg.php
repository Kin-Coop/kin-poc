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

/* modules/contrib/views_fieldsets/templates/views-fieldsets-fieldset.html.twig */
class __TwigTemplate_bc70f9ec20e3f7f6f5905f3cfc10cc31 extends Template
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
        // line 1
        if (($context["show_fieldset"] ?? null)) {
            // line 2
            yield "  <fieldset ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["attributes"] ?? null), "html", null, true);
            yield ">
    ";
            // line 3
            if (($context["legend"] ?? null)) {
                // line 4
                yield "      <legend>";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["legend"] ?? null), "html", null, true);
                yield "</legend>
    ";
            }
            // line 6
            yield "    ";
            yield from $this->loadTemplate("views-view-fields.html.twig", "modules/contrib/views_fieldsets/templates/views-fieldsets-fieldset.html.twig", 6)->unwrap()->yield($context);
            // line 7
            yield "  </fieldset>
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["show_fieldset", "attributes", "legend"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/views_fieldsets/templates/views-fieldsets-fieldset.html.twig";
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
        return array (  62 => 7,  59 => 6,  53 => 4,  51 => 3,  46 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/views_fieldsets/templates/views-fieldsets-fieldset.html.twig", "/home/kin/sites/kin/dir/web/modules/contrib/views_fieldsets/templates/views-fieldsets-fieldset.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 1, "include" => 6];
        static $filters = ["escape" => 2];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'include'],
                ['escape'],
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
