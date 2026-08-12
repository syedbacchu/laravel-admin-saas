<?php

namespace App\Enums;

enum FieldTypeEnum: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case RICHTEXT = 'richtext';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case URL = 'url';
    case IMAGE = 'image';
    case RESPONSIVE_IMAGE = 'responsive_image';
    case SELECT = 'select';
    case RELATION = 'relation';
    case REPEATABLE = 'repeatable';
    case VIDEO = 'video';
    case FILE = 'file';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case COLOR = 'color';
    case GROUP = 'group';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => __('Text'),
            self::TEXTAREA => __('Textarea'),
            self::RICHTEXT => __('Rich Text'),
            self::NUMBER => __('Number'),
            self::BOOLEAN => __('Boolean'),
            self::URL => __('URL'),
            self::IMAGE => __('Image'),
            self::RESPONSIVE_IMAGE => __('Responsive Image'),
            self::SELECT => __('Select'),
            self::RELATION => __('Relation'),
            self::REPEATABLE => __('Repeatable'),
            self::VIDEO => __('Video'),
            self::FILE => __('File'),
            self::DATE => __('Date'),
            self::DATETIME => __('DateTime'),
            self::COLOR => __('Color'),
            self::GROUP => __('Group'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    public static function toSelectArray(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], self::cases());
    }

    public function isRepeatable(): bool
    {
        return $this === self::REPEATABLE;
    }

    public function isParentFieldType(): bool
    {
        return in_array($this, [self::REPEATABLE, self::GROUP]);
    }

    public function requiresConfig(): bool
    {
        return in_array($this, [
            self::REPEATABLE,
            self::NUMBER,
            self::TEXT,
            self::TEXTAREA,
            self::IMAGE,
            self::RESPONSIVE_IMAGE,
            self::FILE,
            self::VIDEO,
            self::SELECT,
            self::RELATION,
        ]);
    }

    public function getConfigFields(): array
    {
        return match ($this) {
            self::REPEATABLE => ['min_items', 'max_items'],
            self::NUMBER => ['min', 'max', 'step'],
            self::TEXT, self::TEXTAREA => ['max_length', 'default'],
            self::IMAGE, self::RESPONSIVE_IMAGE, self::FILE, self::VIDEO => ['max_size', 'allowed_types'],
            self::SELECT, self::RELATION => ['options'],
            default => [],
        };
    }
}