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

/* modules/contrib/captcha/templates/captcha.html.twig */
class __TwigTemplate_5bad012610bd503d4bea72da47a2a7cb extends Template
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
            'captcha' => [$this, 'block_captcha'],
            'captcha_display' => [$this, 'block_captcha_display'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 19
        yield "
";
        // line 21
        $context["classes"] = ["captcha", \Drupal\Component\Utility\Html::getClass(("captcha-type-challenge--" . (($_v0 =         // line 23
($context["element"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#captcha_type_challenge"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["element"] ?? null), "#captcha_type_challenge", [], "array", false, false, true, 23))))];
        // line 26
        yield "
";
        // line 27
        yield from $this->unwrap()->yieldBlock('captcha', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["element", "is_visible", "title", "attributes", "description"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_captcha(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 28
        yield "  ";
        if (($context["is_visible"] ?? null)) {
            // line 29
            yield "    ";
            yield from $this->unwrap()->yieldBlock('captcha_display', $context, $blocks);
            // line 50
            yield "  ";
        } else {
            // line 51
            yield "    ";
            // line 53
            yield "    ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["element"] ?? null), "html", null, true);
            yield "
  ";
        }
        yield from [];
    }

    // line 29
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_captcha_display(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 30
        yield "      ";
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["title"] ?? null))) {
            // line 31
            yield "        <fieldset ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 31), "html", null, true);
            yield ">
          <legend class=\"captcha__title js-form-required form-required\">
            ";
            // line 33
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
            yield "
          </legend>
        ";
        } else {
            // line 36
            yield "          <div ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 36), "html", null, true);
            yield ">
        ";
        }
        // line 38
        yield "          <div class=\"captcha__element\">
            ";
        // line 39
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["element"] ?? null), "html", null, true);
        yield "
          </div>
          ";
        // line 41
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["description"] ?? null))) {
            // line 42
            yield "            <div class=\"captcha__description description\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["description"] ?? null), "html", null, true);
            yield "</div>
          ";
        }
        // line 44
        yield "      ";
        if ( !Twig\Extension\CoreExtension::testEmpty(($context["title"] ?? null))) {
            // line 45
            yield "          </fieldset>
      ";
        } else {
            // line 47
            yield "        </div>
      ";
        }
        // line 49
        yield "    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/captcha/templates/captcha.html.twig";
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
        return array (  142 => 49,  138 => 47,  134 => 45,  131 => 44,  125 => 42,  123 => 41,  118 => 39,  115 => 38,  109 => 36,  103 => 33,  97 => 31,  94 => 30,  87 => 29,  78 => 53,  76 => 51,  73 => 50,  70 => 29,  67 => 28,  55 => 27,  52 => 26,  50 => 23,  49 => 21,  46 => 19,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/captcha/templates/captcha.html.twig", "/home/kin/sites/kin/dir/web/modules/contrib/captcha/templates/captcha.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 21, "block" => 27, "if" => 28];
        static $filters = ["clean_class" => 23, "escape" => 53];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'if'],
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
