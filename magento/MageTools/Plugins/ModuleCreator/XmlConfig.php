<?php

namespace Dahl\MageTools\Plugins\ModuleCreator;
use \SimpleXMLElement;

/**
 * Xml config element
 * 
 * @uses SimpleXMLElement
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class XmlConfig extends SimpleXMLElement
{
    /**
     * Find a descendant of a node by path
     *
     * @param   string $path The xml path.
     * @return  XmlConfig
     */
    public function getNode($path)
    {
        $pathArr = explode('/', $path);
        $desc = $this;
        foreach ($pathArr as $nodeName) {
            $desc = $desc->$nodeName;
            if (!$desc) {
                return false;
            }
        }

        return $desc;
    }

    /**
     * Formats xml node as a string.
     *
     * @param int $level Recursion level
     * @return string
     */
    public function getXml($level = 0)
    {
        $pad = str_pad('', $level * 4, ' ', STR_PAD_LEFT);
        $out = "{$pad}<{$this->getName()}";

        if ($attributes = $this->attributes()) {
            foreach ($attributes as $key => $value) {
                $value = str_replace('"', '\"', (string)$value);
                $out .= " {$key}=\"{$value}\"";
            }
        }

        if (count($this->children())) {
            $out .= ">\n";
            foreach ($this->children() as $child) {
                $out .= $child->getXml($level + 1);
            }
            $out .= "{$pad}</{$this->getName()}>\n";
        } else {
            $value = (string)$this;
            if (strlen($value)) {
                $out .= ">{$this->xmlentities($value)}</{$this->getName()}>\n";
            } else {
                $out .= "/>\n";
            }
        }

        return $out;
    }

    /**
     * Converts meaningful xml characters to xml entities
     *
     * @param  string
     * @return string
     */
    public function xmlentities($value = null)
    {
        if (is_null($value)) {
            $value = $this;
        }
        $value = (string)$value;

        $value = str_replace(
            array('&', '"', "'", '<', '>'),
            array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'),
            $value
        );

        return $value;
    }

    /**
     * Set node value through path.
     * 
     * @param string $path
     * @param string $value
     * @access public
     * @return XmlConfig
     */
    public function setNode($path, $value, $overwrite = false)
    {
        if (!$overwrite && $this->getNode($path)) {
            return $this;
        }
        $arr1 = explode('/', $path);
        $arr = array();
        foreach ($arr1 as $v) {
            if (!empty($v)) {
                $arr[] = $v;
            }
        }
        $last = sizeof($arr) - 1;
        $node = $this;
        foreach ($arr as $i => $nodeName) {
            if ($last === $i) {
                $node->$nodeName = $value;
            } else {
                if (!isset($node->$nodeName)) {
                    $node = $node->addChild($nodeName);
                } else {
                    $node = $node->$nodeName;
                }
            }

        }

        return $this;
    }
}
