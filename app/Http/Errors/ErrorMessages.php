<?php

namespace App\Http\Errors;

final class ErrorMessages
{
    /**
     * @var array<string, string>
     */
    public const MAP = [
        'contact.name.required' => 'A contact name is required.',
        'contact.email.required' => 'A contact email is required.',
        'contact.not_validated' => 'Validate the contact before sending an estimate.',
        'user.required' => 'Choose an acting user.',
        'user.name.required' => 'A user name is required.',
        'user.name.taken' => 'That user name is already used.',
        'user.delete.blocked' => 'That user still owns a contact.',
        'product.name.required' => 'A product name is required.',
        'product.name.taken' => 'That product name is already used.',
        'product.unit_price.invalid' => 'A unit price is a whole number, zero or greater.',
        'product.delete.blocked' => 'That product is on an estimate.',
        'product.category.invalid' => 'Choose a category from the list.',
        'product.description.invalid' => 'Use at most 255 characters.',
        'contact.company_size.invalid' => 'Choose a company size from the list.',
        'contact.owner.invalid' => 'Choose an owner.',
        'contact.description.invalid' => 'Use at most 255 characters.',
        'contact.notes.invalid' => 'Use at most 255 characters.',
        'contact.email.invalid' => 'Enter a valid email address.',
        'contact.company.invalid' => 'Use at most 255 characters.',
        'estimate.lines.required' => 'Add a product quantity.',
        'voice.transcribe.failed' => 'The recording could not be transcribed.',
        'voice.transcript.empty' => 'No words were heard.',
        'voice.fields.required' => 'That field is required.',
        'voice.token.required' => 'The voice token is missing.',
        'voice.run.required' => 'The voice run is missing.',
        'voice.run.missing' => 'The voice run is missing.',
        'voice.tool.unknown' => 'That action is not available.',
        'voice.not_found' => 'That record is missing.',
    ];
}
