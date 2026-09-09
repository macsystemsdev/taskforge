<div style="
    font-family: ui-sans-serif, system-ui, sans-serif;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background-color: #ffffff;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    @if (!empty($metadata['accent_color'])) border-top: 4px solid {{ $metadata['accent_color'] }}; @endif
">

    @if (!empty($metadata['badge']) || !empty($metadata['popular']) || !empty($metadata['recommended']))
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
            @if (!empty($metadata['badge']))
                <span style="background-color: #fef3c7; color: #b45309; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">{{ $metadata['badge'] }}</span>
            @endif

            @if (!empty($metadata['popular']))
                <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Popular</span>
            @endif

            @if (!empty($metadata['recommended']))
                <span style="background-color: #d1fae5; color: #047857; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Recommended</span>
            @endif
        </div>
    @endif

    <div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0;">
            {{ $metadata['display_name'] ?? $plan['name'] }}
        </h3>

        @if (!empty($metadata['subtitle']))
            <p style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0 0;">
                {{ $metadata['subtitle'] }}
            </p>
        @endif
    </div>

    <div style="margin-top: 1.25rem;">
        <p style="font-size: 1.875rem; font-weight: 600; color: #111827; margin: 0;">
            {{ \App\Support\CurrencyFormatter::format($plan['price'], $plan['currency']) }}
            @if (!empty($plan['billing_interval']) && $plan['billing_interval'] !== 'none')
                <span style="font-size: 1rem; font-weight: 400; color: #6b7280;">/ {{ $plan['billing_interval'] }}</span>
            @endif
        </p>

        @if (!empty($plan['currency']))
            <p style="font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin: 0.25rem 0 0 0;">
                Billed in {{ $plan['currency'] }}
            </p>
        @endif
    </div>

    @if (!empty($metadata['description']))
        <p style="font-size: 0.875rem; color: #4b5563; margin-top: 1rem; line-height: 1.5;">
            {{ $metadata['description'] }}
        </p>
    @endif

    <ul style="list-style: none; padding: 0; margin: 1.25rem 0 0 0; display: flex; flex-direction: column; gap: 0.5rem;">
        <li style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #4b5563;">
            <span>Workspaces</span>
            <span style="font-weight: 500;">{{ $plan['max_workspaces'] ?? 'Unlimited' }}</span>
        </li>
        <li style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #4b5563;">
            <span>Projects</span>
            <span style="font-weight: 500;">{{ $plan['max_projects'] ?? 'Unlimited' }}</span>
        </li>
        <li style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #4b5563;">
            <span>Members</span>
            <span style="font-weight: 500;">{{ $plan['max_members'] ?? 'Unlimited' }}</span>
        </li>
    </ul>

    @if (!empty($metadata['marketing_copy']))
        <p style="font-size: 0.875rem; color: #6b7280; margin-top: 1rem; line-height: 1.5;">
            {{ $metadata['marketing_copy'] }}
        </p>
    @endif

    <button disabled style="
        margin-top: 1.5rem;
        width: 100%;
        background-color: #4f46e5;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: none;
        opacity: 0.65;
        cursor: not-allowed;
    ">
        {{ $metadata['button_text'] ?? 'Get Started' }}
    </button>
</div>
