<?php

namespace App\Http\Voice;

final class VoiceCatalog
{
    /**
     * Route names the spoken agent must not call.
     *
     * @var list<string>
     */
    public const EXCLUDED = [
        'users.create',
        'users.csv',
        'users.pdf',
        'contacts.create',
        'contacts.csv',
        'contacts.pdf',
        'products.create',
        'products.csv',
        'products.pdf',
        'estimates.create',
        'estimates.csv',
        'estimates.pdf',
    ];

    /**
     * @return list<array{name: string, route: string, description: string, inputSchema: array<string, mixed>}>
     */
    public static function tools(): array
    {
        return [
            self::tool('home', 'List the acting user\'s contacts on the board, grouped by column.', self::object([
                'query' => self::stringProp('Ignored. The board is the whole set.'),
            ], [])),
            self::tool('acting-user.store', 'Choose the acting user for the rest of this instruction.', self::object([
                'user_id' => self::integerProp('User id from users_index.'),
            ], ['user_id'])),
            self::tool('users.index', 'List users. Search matches the name.', self::object([
                'query' => self::stringProp('Optional name search.'),
            ], [])),
            self::tool('users.show', 'Show one user.', self::object([
                'user_id' => self::integerProp('User id.'),
            ], ['user_id'])),
            self::tool('users.store', 'Create a user.', self::object([
                'name' => self::stringProp('User name.'),
            ], ['name'])),
            self::tool('users.update', 'Rename a user.', self::object([
                'user_id' => self::integerProp('User id.'),
                'name' => self::stringProp('New name.'),
            ], ['user_id', 'name'])),
            self::tool('users.destroy', 'Delete a user who owns no contact.', self::object([
                'user_id' => self::integerProp('User id.'),
            ], ['user_id'])),
            self::tool('contacts.index', 'List the acting user\'s contacts. Search matches the name.', self::object([
                'query' => self::stringProp('Optional name search.'),
            ], [])),
            self::tool('contacts.show', 'Show one contact that belongs to the acting user.', self::object([
                'contact_id' => self::integerProp('Contact id.'),
            ], ['contact_id'])),
            self::tool('contacts.store', 'Create a contact for the acting user.', self::object([
                'name' => self::stringProp('Contact name.'),
            ], ['name'])),
            self::tool('contacts.update', 'Change a contact\'s name, email, and company. The column stays.', self::object([
                'contact_id' => self::integerProp('Contact id.'),
                'name' => self::stringProp('Contact name.'),
                'email' => self::stringProp('Email. Empty is allowed.'),
                'company' => self::stringProp('Company. Empty is allowed.'),
            ], ['contact_id', 'name'])),
            self::tool('contacts.destroy', 'Delete a contact that belongs to the acting user.', self::object([
                'contact_id' => self::integerProp('Contact id.'),
            ], ['contact_id'])),
            self::tool('contacts.qualify', 'Qualify a contact. An empty email stays empty and the column can still move from new to qualified.', self::object([
                'contact_id' => self::integerProp('Contact id.'),
                'email' => self::stringProp('Email. Empty is allowed.'),
                'company' => self::stringProp('Company. Empty is allowed.'),
            ], ['contact_id'])),
            self::tool('contacts.estimates.store', 'Send an estimate for a contact. At least one quantity must be 1 or more.', self::linesObject('Contact id.')),
            self::tool('products.index', 'List products. Search matches the name.', self::object([
                'query' => self::stringProp('Optional name search.'),
            ], [])),
            self::tool('products.show', 'Show one product.', self::object([
                'product_id' => self::integerProp('Product id.'),
            ], ['product_id'])),
            self::tool('products.store', 'Create a product.', self::object([
                'name' => self::stringProp('Product name.'),
                'unit_price' => self::integerProp('Unit price, zero or greater.'),
            ], ['name', 'unit_price'])),
            self::tool('products.update', 'Change a product name and unit price.', self::object([
                'product_id' => self::integerProp('Product id.'),
                'name' => self::stringProp('Product name.'),
                'unit_price' => self::integerProp('Unit price, zero or greater.'),
            ], ['product_id', 'name', 'unit_price'])),
            self::tool('products.destroy', 'Delete a product that is on no estimate line.', self::object([
                'product_id' => self::integerProp('Product id.'),
            ], ['product_id'])),
            self::tool('estimates.index', 'List the acting user\'s estimates. Search matches the contact name.', self::object([
                'query' => self::stringProp('Optional contact name search.'),
            ], [])),
            self::tool('estimates.show', 'Show one estimate that belongs to the acting user.', self::object([
                'estimate_id' => self::integerProp('Estimate id.'),
            ], ['estimate_id'])),
            self::tool('estimates.update', 'Replace an estimate\'s lines. At least one quantity must be 1 or more.', self::linesObject('Estimate id.')),
            self::tool('estimates.destroy', 'Delete an estimate that belongs to the acting user.', self::object([
                'estimate_id' => self::integerProp('Estimate id.'),
            ], ['estimate_id'])),
        ];
    }

    /**
     * @return array{name: string, route: string, description: string, inputSchema: array<string, mixed>}|null
     */
    public static function find(string $name): ?array
    {
        foreach (self::tools() as $tool) {
            if ($tool['name'] === $name) {
                return $tool;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array{name: string, route: string, description: string, inputSchema: array<string, mixed>}
     */
    private static function tool(string $route, string $description, array $schema): array
    {
        return [
            'name' => str_replace('.', '_', $route),
            'route' => $route,
            'description' => $description.' Route '.$route.'.',
            'inputSchema' => $schema,
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    private static function object(array $properties, array $required): array
    {
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function linesObject(string $idDescription): array
    {
        $id = str_contains($idDescription, 'Estimate') ? 'estimate_id' : 'contact_id';

        return self::object([
            $id => self::integerProp($idDescription),
            'lines' => [
                'type' => 'array',
                'description' => 'Quantities. A quantity below 1 is skipped.',
                'items' => self::object([
                    'product_id' => self::integerProp('Product id.'),
                    'quantity' => self::integerProp('Quantity.'),
                ], ['product_id', 'quantity']),
            ],
        ], [$id, 'lines']);
    }

    /**
     * @return array<string, string>
     */
    private static function stringProp(string $description): array
    {
        return [
            'type' => 'string',
            'description' => $description,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function integerProp(string $description): array
    {
        return [
            'type' => 'integer',
            'description' => $description,
        ];
    }
}
