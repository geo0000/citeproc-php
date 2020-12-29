<?php
/*
 * citeproc-php
 *
 * @link        http://github.com/seboettg/citeproc-php for the source repository
 * @copyright   Copyright (c) 2016 Sebastian Böttger.
 * @license     https://opensource.org/licenses/MIT
 */

namespace Seboettg\CiteProc\Style;

use Seboettg\CiteProc\Data\DataList;
use Seboettg\CiteProc\Exception\InvalidStylesheetException;
use Seboettg\CiteProc\Rendering\HasParent;
use Seboettg\CiteProc\Rendering\Rendering;
use Seboettg\CiteProc\Styles\ConsecutivePunctuationCharacterTrait;
use Seboettg\CiteProc\Util\Factory;
use Seboettg\Collection\ArrayList as ArrayList;
use Seboettg\Collection\ArrayList\ArrayListInterface;
use SimpleXMLElement;

/**
 * Class Macro
 *
 * Macros, defined with cs:macro elements, contain formatting instructions. Macros can be called with cs:text from
 * within other macros and the cs:layout element of cs:citation and cs:bibliography, and with cs:key from within cs:sort
 * of cs:citation and cs:bibliography. It is recommended to place macros after any cs:locale elements and before the
 * cs:citation element.
 *
 * Macros are referenced by the value of the required name attribute on cs:macro. The cs:macro element must contain one
 * or more rendering elements.
 *
 * @package Seboettg\CiteProc\Rendering
 *
 * @author Sebastian Böttger <seboettg@gmail.com>
 */
class Macro implements Rendering, HasParent
{
    use ConsecutivePunctuationCharacterTrait;

    /**
     * @var ArrayList
     */
    private $children;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $parent;

    /**
     * @param SimpleXMLElement $node
     * @param $parent
     * @return Macro
     * @throws InvalidStylesheetException
     */
    public static function factory(SimpleXMLElement $node, $parent): Macro
    {
        $name = (string) $node->attributes()['name'];
        $children = new ArrayList();
        foreach ($node->children() as $child) {
            $children->append(Factory::create($child, $parent));
        }
        return new Macro($children, $parent, $name);
    }

    /**
     * Macro constructor.
     * @param ArrayListInterface $children
     * @param mixed $parent
     * @param $name
     */
    public function __construct(ArrayListInterface $children, $parent, $name)
    {
        $this->children = $children;
        $this->parent = $parent;
        $this->name = $name;
    }

    /**
     * @param array|DataList $data
     * @param int|null $citationNumber
     * @return mixed
     */
    public function render($data, $citationNumber = null)
    {
        $ret = [];
        /** @var Rendering $child */
        foreach ($this->children as $child) {
            $res = $child->render($data, $citationNumber);
            $this->getChildrenAffixesAndDelimiter($child);
            if (!empty($res)) {
                $ret[] = $res;
            }
        }
        $res = implode("", $ret);
        if (!empty($res)) {
            $res = $this->removeConsecutiveChars($res);
        }
        return $res;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}
