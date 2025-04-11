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

/* field.html.twig */
class __TwigTemplate_8062efaa3c48a0eb89e2d3fc93ea33ea extends Template
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
            'field' => [$this, 'block_field'],
            'field_label' => [$this, 'block_field_label'],
            'field_label_value' => [$this, 'block_field_label_value'],
            'field_items' => [$this, 'block_field_items'],
            'field_item' => [$this, 'block_field_item'],
            'field_value' => [$this, 'block_field_value'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 8
        $context["classes"] = ["field", ("field--name-" . \Drupal\Component\Utility\Html::getClass(        // line 10
($context["field_name"] ?? null))), ("field--type-" . \Drupal\Component\Utility\Html::getClass(        // line 11
($context["field_type"] ?? null))), ("field--label-" .         // line 12
($context["label_display"] ?? null)), (( !        // line 13
($context["display_items_wrapper_tag"] ?? null)) ? ("field__items") : (""))];
        // line 17
        $context["title_classes"] = ["field__label", (((        // line 19
($context["label_display"] ?? null) == "visually_hidden")) ? ("visually-hidden") : (""))];
        // line 22
        yield from $this->unwrap()->yieldBlock('field', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["field_name", "field_type", "label_display", "display_items_wrapper_tag", "display_field_tag", "field_tag", "attributes", "label_hidden", "display_label_tag", "label_tag", "title_attributes", "label", "field_items_wrapper_tag", "field_items_wrapper_attributes", "items", "display_item_tag", "field_item_tag"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 23
        if (($context["display_field_tag"] ?? null)) {
            yield "
  <";
            // line 24
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 24), "html", null, true);
            yield ">";
        }
        // line 26
        if ( !($context["label_hidden"] ?? null)) {
            // line 27
            yield from $this->unwrap()->yieldBlock('field_label', $context, $blocks);
        }
        // line 37
        yield from $this->unwrap()->yieldBlock('field_items', $context, $blocks);
        // line 58
        if (($context["display_field_tag"] ?? null)) {
            yield "
  </";
            // line 59
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield ">";
        }
        yield from [];
    }

    // line 27
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field_label(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "
    ";
        // line 28
        if (($context["display_label_tag"] ?? null)) {
            yield "<";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("label_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["label_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["title_attributes"] ?? null), "addClass", [($context["title_classes"] ?? null)], "method", false, false, true, 28), "html", null, true);
            yield ">";
        }
        // line 29
        yield from $this->unwrap()->yieldBlock('field_label_value', $context, $blocks);
        // line 32
        if (($context["display_label_tag"] ?? null)) {
            // line 33
            yield "</";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("label_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["label_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield ">";
        }
        yield from [];
    }

    // line 29
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field_label_value(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 30
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
        yield from [];
    }

    // line 37
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field_items(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 38
        if (($context["display_items_wrapper_tag"] ?? null)) {
            yield "
    <";
            // line 39
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_items_wrapper_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_items_wrapper_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["field_items_wrapper_attributes"] ?? null), "addClass", ["field__items"], "method", false, false, true, 39), "html", null, true);
            yield ">";
        }
        // line 41
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 42
            yield from $this->unwrap()->yieldBlock('field_item', $context, $blocks);
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 54
        if (($context["display_items_wrapper_tag"] ?? null)) {
            yield "
    </";
            // line 55
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_items_wrapper_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_items_wrapper_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield ">";
        }
        yield from [];
    }

    // line 42
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field_item(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 43
        if (($context["display_item_tag"] ?? null)) {
            yield "
        <";
            // line 44
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_item_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_item_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "attributes", [], "any", false, false, true, 44), "addClass", ["field__item"], "method", false, false, true, 44), "html", null, true);
            yield ">";
        }
        // line 46
        yield from $this->unwrap()->yieldBlock('field_value', $context, $blocks);
        // line 49
        if (($context["display_item_tag"] ?? null)) {
            // line 50
            yield "</";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("field_item_tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["field_item_tag"] ?? null), "div")) : ("div")), "html", null, true);
            yield ">";
        }
        yield from [];
    }

    // line 46
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_field_value(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 47
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "content", [], "any", false, false, true, 47), "html", null, true);
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "field.html.twig";
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
        return array (  231 => 47,  224 => 46,  216 => 50,  214 => 49,  212 => 46,  207 => 44,  203 => 43,  196 => 42,  189 => 55,  185 => 54,  171 => 42,  154 => 41,  149 => 39,  145 => 38,  138 => 37,  133 => 30,  126 => 29,  118 => 33,  116 => 32,  114 => 29,  107 => 28,  98 => 27,  91 => 59,  87 => 58,  85 => 37,  82 => 27,  80 => 26,  75 => 24,  71 => 23,  59 => 22,  57 => 19,  56 => 17,  54 => 13,  53 => 12,  52 => 11,  51 => 10,  50 => 8,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "field.html.twig", "modules/contrib/fences/templates/field.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 8, "block" => 22, "if" => 23, "for" => 41];
        static $filters = ["clean_class" => 10, "escape" => 24, "default" => 24];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'if', 'for'],
                ['clean_class', 'escape', 'default'],
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
