<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-13 8:5 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: GenderEnum
 */

namespace Maatify\Portal\Generals\Gender;


enum GenderEnum: int
{
    case Undefined = 0;
    case Male = 1;
    case Female = 2;

    /**
     * Validate and get the corresponding EnumAppTypeId case.
     *
     * @param   int  $type_id
     *
     * @return ?self
     */
    public static function validate(int $type_id): ?self
    {
        return self::tryFrom($type_id);
    }

    /**
     * Get an array of cases with their ID as key and name as value.
     *
     * @return array<int, string>
     */
    public static function getIdNamePairs(): array
    {
        $pairs = [];
        foreach (self::cases() as $case) {
            $pairs[$case->value] = $case->name;
        }
        return $pairs;
    }

    /**
     * Get an array of cases with 'id' as the case value and 'value' as the case name.
     *
     * @return array<array<string, int|string>>
     */
    public static function getIdValuePairs(): array
    {
        $pairs = [];
        foreach (self::cases() as $case) {
            $pairs[] = [
                'id' => $case->value,
                'value' => $case->name,
            ];
        }
        return $pairs;
    }
}