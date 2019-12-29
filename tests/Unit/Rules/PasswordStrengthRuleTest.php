<?php

namespace Tests\Unit\Rules;

use App\Rules\PasswordStrengthRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PasswordStrengthRuleTest extends TestCase
{
    /** @test */
    public function itFailsWhenPasswordTooShort()
    {
        $outcome = $this->validateAgainstRule('Te34567');
        $this->assertFails($outcome);
        $this->assertMessageEquals($outcome, trans('validation.min.string', ['attribute' => 'password', 'min' => 8]));
    }

    /** @test */
    public function itFailsWhenPasswordDoesNotContainAUppercaseCharacter()
    {
        $outcome = $this->validateAgainstRule('testing89');
        $this->assertFails($outcome);
        $this->assertMessageEquals($outcome, trans('validation.uppercase', ['attribute' => 'password', 'min' => 1]));
    }

    /** @test */
    public function itFailsWhenPasswordDoesNotContainALowercaseCharacter()
    {
        $outcome = $this->validateAgainstRule('TESTING89');
        $this->assertFails($outcome);
        $this->assertMessageEquals($outcome, trans('validation.lowercase', ['attribute' => 'password', 'min' => 1]));
    }

    /** @test */
    public function itFailsWhenPasswordDoesNotContainANumericalCharacter()
    {
        $outcome = $this->validateAgainstRule('TeSTINGTE');
        $this->assertFails($outcome);
        $this->assertMessageEquals($outcome, trans('validation.numbers', ['attribute' => 'password', 'min' => 1]));
    }

    /** @test */
    public function itPassesCorrectly()
    {
        $this->assertPasses($this->validateAgainstRule('Testing123'));
        $this->assertPasses($this->validateAgainstRule('IL1kE@uth'));
        $this->assertPasses($this->validateAgainstRule('LT8$iN&8t'));
    }

    /*
     * Helper Functions
     */

    private function validateAgainstRule($password): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make([
            'password' => $password,
        ], [
            'password' => new PasswordStrengthRule(),
        ]);
    }

    private function assertFails(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->assertTrue($validator->fails());
    }

    private function assertPasses(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->assertFalse($validator->fails());
    }

    private function assertMessageEquals(\Illuminate\Contracts\Validation\Validator $validator, $message)
    {
        $this->assertEquals($message, $validator->errors()->get('password')[0]);
    }
}
