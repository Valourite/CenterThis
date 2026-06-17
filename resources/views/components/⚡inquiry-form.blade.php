<?php

use App\Mail\NewInquiryReceived;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $inquiryTopic = '';

    public string $hireStartDate = '';

    public string $hireEndDate = '';

    public string $location = '';

    public string $message = '';

    public string $website = '';

    public bool $submitted = false;

    /**
     * @return list<string>
     */
    public function inquiryTopics(): array
    {
        return [
            'Item availability',
            'Product details and quantities',
            'Hire process',
            'Collection and returns',
            'Bespoke items',
            'General question',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'inquiryTopic' => ['required', 'string', Rule::in($this->inquiryTopics())],
            'hireStartDate' => ['nullable', 'required_with:hireEndDate', 'date', 'after_or_equal:today'],
            'hireEndDate' => ['nullable', 'date', 'after_or_equal:hireStartDate'],
            'location' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'website' => ['max:0'],
        ]);

        $inquiry = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => ($validated['phone'] ?? '') ?: null,
            'inquiry_topic' => $validated['inquiryTopic'],
            'hire_start_date' => ($validated['hireStartDate'] ?? '') ?: null,
            'hire_end_date' => ($validated['hireEndDate'] ?? '') ?: null,
            'location' => ($validated['location'] ?? '') ?: null,
            'message' => $validated['message'],
        ];

        Mail::to(
            (string) config('inquiries.recipient.address'),
            (string) config('inquiries.recipient.name'),
        )->send(new NewInquiryReceived($inquiry));

        $this->reset([
            'name',
            'email',
            'phone',
            'inquiryTopic',
            'hireStartDate',
            'hireEndDate',
            'location',
            'message',
            'website',
        ]);

        $this->submitted = true;
    }
};
?>

<div class="rounded-4xl bg-ink p-5 text-white shadow-2xl shadow-black/15 sm:p-8 lg:p-10">
    @if ($submitted)
        <div class="flex min-h-96 flex-col items-start justify-between gap-10" role="status">
            <div class="flex size-14 items-center justify-center rounded-full bg-accent text-ink">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="max-w-xl">
                <p class="mb-3 text-xs font-semibold tracking-[0.22em] text-accent uppercase">Inquiry received</p>
                <h3 class="font-display text-4xl leading-none sm:text-5xl">Your inquiry has been received.</h3>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/65">
                    Thank you for getting in touch. We will review your question and respond with the relevant product or business information.
                </p>
            </div>

            <button
                type="button"
                wire:click="$set('submitted', false)"
                class="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-3 text-sm font-semibold transition hover:border-white hover:bg-white hover:text-ink"
            >
                Send another inquiry
            </button>
        </div>
    @else
        <form wire:submit="submit" class="grid gap-5">
            <div class="mb-2">
                <p class="text-xs font-semibold tracking-[0.22em] text-accent uppercase">How can we help?</p>
                <h3 class="mt-3 font-display text-4xl leading-none sm:text-5xl">Ask about an item or our business.</h3>
                <p class="mt-4 max-w-xl text-sm leading-6 text-white/60">Use this form for questions about available decor, product details, hire dates, collection and returns, bespoke items, or CenterThis generally.</p>
            </div>

            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field label="Your name" name="name">
                    <input id="name" type="text" wire:model.blur="name" autocomplete="name" class="form-control" placeholder="e.g. Lerato Mokoena">
                </x-form.field>

                <x-form.field label="Email address" name="email">
                    <input id="email" type="email" wire:model.blur="email" autocomplete="email" class="form-control" placeholder="you@example.com">
                </x-form.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field label="Phone number" name="phone">
                    <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel" class="form-control" placeholder="+27">
                </x-form.field>

                <x-form.field label="What is your inquiry about?" name="inquiryTopic">
                    <select id="inquiryTopic" wire:model.blur="inquiryTopic" class="form-control">
                        <option value="">Choose a topic</option>
                        @foreach ($this->inquiryTopics() as $topic)
                            <option wire:key="inquiry-topic-{{ $loop->index }}">{{ $topic }}</option>
                        @endforeach
                    </select>
                </x-form.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-form.field label="Hire start date (optional)" name="hireStartDate">
                    <input id="hireStartDate" type="date" wire:model.blur="hireStartDate" min="{{ now()->toDateString() }}" class="form-control">
                </x-form.field>

                <x-form.field label="Hire end date (optional)" name="hireEndDate">
                    <input id="hireEndDate" type="date" wire:model.blur="hireEndDate" min="{{ $hireStartDate ?: now()->toDateString() }}" class="form-control">
                </x-form.field>

                <x-form.field label="Your area (optional)" name="location">
                    <input id="location" type="text" wire:model.blur="location" autocomplete="address-level2" class="form-control" placeholder="e.g. Centurion">
                </x-form.field>
            </div>

            <x-form.field label="Your question" name="message">
                <textarea id="message" wire:model.blur="message" rows="5" class="form-control resize-none" placeholder="Tell us which item you are interested in, the quantity you need, or what you would like to know..."></textarea>
            </x-form.field>

            <div class="flex flex-col items-start justify-between gap-4 pt-2 sm:flex-row sm:items-center">
                <p class="max-w-md text-xs leading-5 text-white/45">By submitting, you agree that we may contact you about this inquiry.</p>
                <button type="submit" class="group inline-flex min-w-48 items-center justify-center gap-3 rounded-full bg-accent px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-white disabled:cursor-wait disabled:opacity-60" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Send inquiry</span>
                    <span wire:loading wire:target="submit">Sending...</span>
                    <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>
    @endif
</div>
