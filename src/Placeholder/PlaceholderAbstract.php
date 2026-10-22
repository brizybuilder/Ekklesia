<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\MonkCms;
use BrizyPlaceholders\AbstractPlaceholder;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;
use BrizyPlaceholders\Replacer;
use Exception;
use Twig_Environment;

abstract class PlaceholderAbstract extends AbstractPlaceholder
{
    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var MonkCms
     */
    protected $monkCMS;

    /**
     * @var null
     */
    protected $group;

    /**
     * @var Replacer|null
     */
    protected $replacer;

    public function __construct(MonkCms $monkCMS, Twig_Environment $twig, ?Replacer $replacer = null)
    {
        $this->twig     = $twig;
        $this->monkCMS  = $monkCMS;
        $this->replacer = $replacer;
    }

    /**
     * @param ContextInterface $context
     * @param ContentPlaceholder $placeholder
     * @return string
     * @throws Exception
     */
    public function getValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        ob_start(); ob_clean();
        $this->echoValue($context, $placeholder);
        return ob_get_clean();
    }

    /**
     * @param ContextInterface $context
     * @param ContentPlaceholder $placeholder
     * @return void
     * @throws Exception
     */
    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder) {
        throw new Exception('Rewrite it in the child class: ' . get_called_class());
    }

    /**
     * This function is required only for the wp installations.
     * @return null
     */
    public function getGroup() {
        return $this->group;
    }

	public function getConfigStructure()
	{
		// TODO: Implement getConfigStructure() method.
	}

	public function getLabel()
	{
		// TODO: Implement getLabel() method.
	}

	public function setLabel($label)
	{
		// TODO: Implement setLabel() method.
	}

	public function getPlaceholder()
	{
        return static::NAME;
	}

	public function setPlaceholder($placeholder)
	{
		// TODO: Implement setPlaceholder() method.
	}

	public function getAttributes()
	{
		// TODO: Implement getAttributes() method.
	}
}