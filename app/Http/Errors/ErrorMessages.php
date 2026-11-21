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
        'user.required' => 'Choose an acting user.',
        'user.name.required' => 'A user name is required.',
        'user.name.taken' => 'That user name is already used.',
        'user.delete.blocked' => 'That user still owns a contact.',
        'product.name.required' => 'A product name is required.',
        'product.name.taken' => 'That product name is already used.',
        'product.unit_price.invalid' => 'A unit price is a whole number, zero or greater.',
        'product.delete.blocked' => 'That product is on an estimate.',
        'estimate.lines.required' => 'Add a product quantity.',
        'voice.clip.required' => 'Hold the button while you speak.',
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
