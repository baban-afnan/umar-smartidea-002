<x-app-layout>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title text-success fw-bold">Notification Center</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active text-dark">Notifications</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row align-items-stretch g-4">
            <!-- Marketing/Info Section -->
            <div class="col-lg-5 d-flex">
                <div class="card border-0 shadow-lg rounded-4 w-100 marketing-card overflow-hidden" 
                     style="background: linear-gradient(135deg, #0d5c3e 0%, #17a673 100%);">
                    <div class="card-body p-5 d-flex flex-column justify-content-center text-white">
                        <div class="mb-4">
                            <i class="ti ti-mail-bulk display-1 text-white opacity-75"></i>
                        </div>
                        <h2 class="fw-bold mb-4 display-6">Engage Your Audience Instantly</h2>
                        <p class="lead mb-5 opacity-75">
                            Leverage the power of direct communication. Whether it's a critical update or a new feature announcement, 
                            reach your users where they are.
                        </p>
                        
                        <div class="features-list">
                            <div class="d-flex align-items-start mb-4">
                                <span class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-users text-success fs-15"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">Send to Everyone</h5>
                                    <p class="small mb-0 opacity-75">Broadcast announcements to your entire active user base with one click.</p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-4">
                                <span class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-user-check text-success fs-15"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">Targeted Communication</h5>
                                    <p class="small mb-0 opacity-75">Reach specific users for account-related updates or personalized messages.</p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <span class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-layout-dashboard text-success fs-15"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">Premium Branding</h5>
                                    <p class="small mb-0 opacity-75">All emails are automatically wrapped in our professional, mobile-responsive template.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="col-lg-7 d-flex">
                <div class="card border-0 shadow-lg rounded-4 w-100">
                    <div class="card-header bg-white border-bottom py-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success-subtle p-3 rounded-3 me-3 text-success">
                                <i class="ti ti-pencil-plus fs-2"></i>
                            </div>
                            <div>
                                <h4 class="card-title mb-0 fw-bold">Compose Notification</h4>
                                <p class="text-muted small mb-0">Craft your message below</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="notificationForm" action="{{ route('admin.notifications.send') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Recipients</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="selection-card form-check custom-radio d-flex align-items-center p-3 border rounded-3 transition-bg cursor-pointer hover-bg-light active-selection" id="allUsersCard">
                                            <input class="form-check-input me-3" type="radio" name="target" id="targetAll" value="all" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="targetAll">
                                                <span class="d-block fw-bold">Send to All Users</span>
                                                <span class="text-muted small">Queue for all active members</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="selection-card form-check custom-radio d-flex align-items-center p-3 border rounded-3 transition-bg cursor-pointer hover-bg-light" id="singleUserCard">
                                            <input class="form-check-input me-3" type="radio" name="target" id="targetSingle" value="single">
                                            <label class="form-check-label w-100 cursor-pointer" for="targetSingle">
                                                <span class="d-block fw-bold">Individual User</span>
                                                <span class="text-muted small">Specify exactly who to reach</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('target')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="singleUserSection" class="mb-4" style="display: none;">
                                <label class="form-label fw-bold text-dark">Select User</label>
                                <select name="user_id" id="userSelect" class="form-control select2-custom">
                                    <option value="">Search by name or email...</option>
                                </select>
                                @error('user_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Subject Line</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="ti ti-heading"></i>
                                    </span>
                                    <input type="text" name="subject" id="subjectInput" class="form-control border-start-0 ps-1" placeholder="e.g., Important Account Update" value="{{ old('subject') }}" required>
                                </div>
                                @error('subject')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Email Message</label>
                                <textarea name="message" id="messageInput" class="form-control" rows="8" placeholder="Start typing your powerful message here..." required>{{ old('message') }}</textarea>
                                <div class="form-text text-muted">You can use Markdown or plain text. Line breaks will be preserved.</div>
                                @error('message')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="d-grid pt-2">
                                <button type="button" id="sendBtn" class="btn btn-success btn-lg rounded-pill fw-bold shadow-sm" style="background-color: #0d5c3e; border: none;">
                                    <i class="ti ti-mail-forward me-2 fs-12"></i> Launch Notification
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .marketing-card {
            transition: transform 0.3s ease;
        }
        .marketing-card:hover {
            transform: translateY(-5px);
        }
        .transition-bg {
            transition: all 0.2s ease;
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }
        .selection-card {
            border: 2px solid #dee2e6 !important;
        }
        .selection-card.active-selection {
            border-color: #0d5c3e !important;
            background-color: rgba(13, 92, 62, 0.05);
        }
        .custom-radio input[type="radio"]:checked + label {
            color: #0d5c3e;
        }
        .form-check-input:checked {
            background-color: #0d5c3e;
            border-color: #0d5c3e;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem !important;
            min-height: 48px !important;
            display: flex !important;
            align-items: center !important;
            border: 1px solid #dee2e6 !important;
        }
        .fs-15 { font-size: 1.5rem; }
        .fs-12 { font-size: 1.2rem; }
    </style>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Success Session Message with SweetAlert
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sent!',
                text: "{!! session('success') !!}",
                confirmButtonColor: '#0d5c3e'
            });
        @endif

        // Error Session Message with SweetAlert
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{!! session('error') !!}",
                confirmButtonColor: '#0d5c3e'
            });
        @endif

        // Toggle user selection field and active classes
        $('input[name="target"]').on('change', function() {
            $('.selection-card').removeClass('active-selection');
            $(this).closest('.selection-card').addClass('active-selection');
            
            if ($(this).val() === 'single') {
                $('#singleUserSection').slideDown();
            } else {
                $('#singleUserSection').slideUp();
            }
        });

        // Initialize state based on initial checked radio
        const initialTarget = $('input[name="target"]:checked').val();
        if (initialTarget === 'single') {
            $('#singleUserSection').show();
            $('#singleUserCard').addClass('active-selection');
            $('#allUsersCard').removeClass('active-selection');
        } else {
            $('#allUsersCard').addClass('active-selection');
        }

        // Click handler for selection cards
        $('.selection-card').on('click', function(e) {
            // Prevent double triggering if clicking the input or label directly
            if (!$(e.target).is('input')) {
                $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
            }
        });

        // Initialize Select2 with dynamic searching
        $('#userSelect').select2({
            ajax: {
                url: '{{ route("admin.notifications.search-user") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.first_name + ' ' + (item.last_name || '') + ' (' + item.email + ')',
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Search for user by name or email...',
            minimumInputLength: 2,
            theme: 'bootstrap-5',
            dropdownParent: $('#singleUserSection')
        });

        // Send Button Confirmation
        $('#sendBtn').on('click', function() {
            const target = $('input[name="target"]:checked').val();
            const subject = $('#subjectInput').val();
            const message = $('#messageInput').val();

            if (!subject || !message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please provide both a subject and a message.',
                    confirmButtonColor: '#0d5c3e'
                });
                return;
            }

            if (target === 'single' && !$('#userSelect').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'User Not Selected',
                    text: 'Please select a recipient for the individual notification.',
                    confirmButtonColor: '#0d5c3e'
                });
                return;
            }

            if (target === 'all') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to send an email to ALL active users. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0d5c3e',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Send All!',
                    cancelButtonText: 'Wait, go back'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#notificationForm').submit();
                    }
                });
            } else {
                $('#notificationForm').submit();
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
