<?php

use App\Mail\NewInquiryReceived;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('emails a valid product inquiry to the configured recipient', function () {
    Mail::fake();

    config()->set('inquiries.recipient.address', 'hello@centerthis.test');
    config()->set('inquiries.recipient.name', 'CenterThis');
    config()->set('inquiries.subject', 'New website inquiry');

    $hireStartDate = now()->addMonths(3)->toDateString();
    $hireEndDate = now()->addMonths(3)->addDays(2)->toDateString();

    Livewire::test('inquiry-form')
        ->set('name', 'Lerato Mokoena')
        ->set('email', 'lerato@example.com')
        ->set('phone', '0825550101')
        ->set('inquiryTopic', 'Item availability')
        ->set('hireStartDate', $hireStartDate)
        ->set('hireEndDate', $hireEndDate)
        ->set('location', 'Centurion')
        ->set('message', 'Are 120 clear Tiffany chairs available for these hire dates?')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSee('Your inquiry has been received.');

    Mail::assertSent(NewInquiryReceived::class, function (NewInquiryReceived $mail) use ($hireStartDate, $hireEndDate): bool {
        return $mail->hasTo('hello@centerthis.test')
            && $mail->hasReplyTo('lerato@example.com')
            && $mail->hasSubject('New website inquiry')
            && $mail->inquiry['name'] === 'Lerato Mokoena'
            && $mail->inquiry['inquiry_topic'] === 'Item availability'
            && $mail->inquiry['hire_start_date'] === $hireStartDate
            && $mail->inquiry['hire_end_date'] === $hireEndDate
            && $mail->inquiry['message'] === 'Are 120 clear Tiffany chairs available for these hire dates?';
    });
});

it('validates required inquiry details', function () {
    Mail::fake();

    Livewire::test('inquiry-form')
        ->call('submit')
        ->assertHasErrors([
            'name' => ['required'],
            'email' => ['required'],
            'inquiryTopic' => ['required'],
            'message' => ['required'],
        ]);

    Mail::assertNothingSent();
});

it('requires a valid hire date range when dates are provided', function () {
    Mail::fake();

    Livewire::test('inquiry-form')
        ->set('name', 'Lerato Mokoena')
        ->set('email', 'lerato@example.com')
        ->set('inquiryTopic', 'Item availability')
        ->set('hireStartDate', now()->addMonth()->toDateString())
        ->set('hireEndDate', now()->addMonth()->subDay()->toDateString())
        ->set('message', 'Please confirm whether your table linen is available for these dates.')
        ->call('submit')
        ->assertHasErrors(['hireEndDate' => ['after_or_equal']]);

    Mail::assertNothingSent();
});

it('rejects inquiry honeypot submissions', function () {
    Mail::fake();

    Livewire::test('inquiry-form')
        ->set('name', 'Automated Sender')
        ->set('email', 'bot@example.com')
        ->set('inquiryTopic', 'General question')
        ->set('message', 'This submission has enough text to pass the normal message validation.')
        ->set('website', 'https://spam.example')
        ->call('submit')
        ->assertHasErrors(['website' => ['max']]);

    Mail::assertNothingSent();
});
