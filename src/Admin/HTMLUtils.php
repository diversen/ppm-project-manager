<?php

declare(strict_types=1);

namespace App\Admin;

class HTMLUtils
{
    /**
     * Get a reference link
     */
    public static function getReferenceLink(string $column, array $references, string $value): ?string
    {
        if (isset($references[$column])) {
            $ref = explode('.', $references[$column]);
            return "/admin/table/$ref[0]/view/$value";
        }
        return null;
    }

    /**
     * Get a reference link in HTML format or just the column value
     */
    public static function getReferenceLinkHTMLOrValue(string $column, array $references, string $value): string
    {
        return self::getReferenceLinkHTML($column, $references, $value) ?? $value;
    }

    /**
     * Get a reference link in HTML format or just the column value
     */
    public static function getReferenceLinkHTML(string $column, array $references, string $value): ?string
    {
        $link = self::getReferenceLink($column, $references, $value);
        if ($link) {
            return "<a href='$link'>$value</a>";
        }

        return null;
    }

    /**
     * Get a HTML breadcrumb for edit / view / delete pages
     */
    public static function getBreadcrumb(string $action, string $table_human): string
    {
        $return_to_link = $_SERVER['HTTP_REFERER'] ?? '/admin';
        $str = "<a href='/admin'>Admin</a> " . ADMIN_SUB_MENU_SEP . ' ';
        $str .= "<a href='$return_to_link'>$table_human</a> " . ADMIN_SUB_MENU_SEP . ' ';
        $str .= $action;
        return $str;
    }

    /**
     * Get html attr disabled from a column name and a disabled array
     */
    public static function isDisabled(string $column, array $disabled): ?string
    {
        if (in_array($column, $disabled)) {
            return 'disabled';
        }
        return null;
    }

    /**
     * Get html attrubtes string from array of attributes
     */
    public static function getAttributesString(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            $attrs[$key] = "$key='$value'";
        }
        return implode(' ', $attrs);
    }

    public static function getHTMLElement(string $column_type, string $value, array $attrs, string $label): string
    {
        $html = '';

        $attrs_label = ['for' => $attrs["name"]];
        $attrs_label_str = self::getAttributesString($attrs_label);

        if ($column_type === 'text') {
            unset($attrs['type'], $attrs['value']);

            $attrs_string = self::getAttributesString($attrs);

            $html .= "<label $attrs_label_str>$label</label>";
            $html .= "<textarea $attrs_string>$value</textarea>";
        }

        if ($column_type === 'tinyint') {
            $attrs['value'] = 1;
            $attrs['type'] = 'checkbox';
            if ($value) {
                $attrs['checked'] = 'checked';
            }

            $attr_string = self::getAttributesString($attrs);

            $html .= "<input $attr_string >";
            $html .= "<label $attrs_label_str>$label</label><br />";
        }

        if ($column_type === 'int') {
            unset($attrs['value']);

            $attrs['type'] = 'number';
            $attrs['value'] = $value;
            $attr_string = self::getAttributesString($attrs);

            $html .= "<label $attrs_label_str>$label</label>";
            $html .= "<input $attr_string >";
        }

        if (in_array($column_type, ['varchar', 'timestamp', 'datetime', 'date', 'time'])) {
            $attrs['type'] = 'text';
            $attrs['value'] = $value;
            $attr_string = self::getAttributesString($attrs);

            $html .= "<label $attrs_label_str>$label</label>";
            $html .= "<input $attr_string >";
        }

        return $html;
    }
}
