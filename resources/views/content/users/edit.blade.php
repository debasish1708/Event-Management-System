@extends('layouts.layoutMaster')

@section('title', 'User Update - Forms')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@vite([
  'resources/assets/vendor/libs/toastr/toastr.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@vite(['resources/assets/vendor/libs/toastr/toastr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite([
    'resources/assets/js/forms-selects.js',
    'resources/assets/js/forms-pickers.js'
])
@endsection

@section('content')
<!-- Multi Column with Form Separator -->
<div class="row">
  <!-- Form Separator -->
  <div class="col-xxl">
    <div class="card mb-6">
      <h5 class="card-header">Form Separator</h5>
      <form class="card-body" action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <h6>1. Account Details</h6>
        <div class="row mb-6">
          <label class="col-sm-3 col-form-label" for="multicol-username">Username</label>
          <div class="col-sm-9">
            <input type="text" id="multicol-username" class="form-control" name="user_name" value="{{ old('user_name', $user->user_name) }}" placeholder="john.doe" />
            @error('user_name')
                <p class="text-danger">{{ $errors->first('user_name') }}</p>
            @enderror
          </div>
        </div>
        <div class="row mb-6">
          <label class="col-sm-3 col-form-label" for="multicol-email">Email</label>
          <div class="col-sm-9">
            <div class="input-group input-group-merge">
              <input type="text" id="multicol-email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="john.doe@gmail.com" />
              @error('email')
                <p class="text-danger">{{ $errors->first('email') }}</p>
              @enderror
            </div>
          </div>
        </div>
        <div class="row form-password-toggle">
          <label class="col-sm-3 col-form-label" for="multicol-password">Password</label>
          <div class="col-sm-9">
            <div class="input-group input-group-merge">
              <input type="password" id="multicol-password" name="password" value="{{ old('password') }}" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="multicol-password2" />
              <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          @error('password')
            <p class="text-danger">{{ $errors->first('password') }}</p>
          @enderror
        </div>
        <hr class="my-6 mx-n4" />
        <h6>2. Personal Info</h6>
        <div class="row mb-6">
          <label class="col-sm-3 col-form-label" for="multicol-full-name">Full Name</label>
          <div class="col-sm-9">
            <input type="text" id="multicol-full-name" class="form-control" name="name" value="{{ old('name', $user->name) }}" placeholder="John Doe" />
            @error('name')
              <p class="text-danger">{{ $errors->first('name') }}</p>
            @enderror
          </div>
        </div>
        <div class="row mb-6">
            <label class="col-sm-3 col-form-label" for="multicol-country">Country</label>
            <div class="col-sm-9">
                <select id="multicol-country" class="select2 form-select" name="country" data-allow-clear="true">
                <option value="">Select</option>
                @php
                    $countries = [
                    'Australia', 'Bangladesh', 'Belarus', 'Brazil', 'Canada', 'China', 'France',
                    'Germany', 'India', 'Indonesia', 'Israel', 'Italy', 'Japan',
                    'Korea' => 'Korea, Republic of', 'Mexico', 'Philippines', 'Russia' => 'Russian Federation',
                    'South Africa', 'Thailand', 'Turkey', 'Ukraine', 'United Arab Emirates',
                    'United Kingdom', 'United States'
                    ];
                    $selectedCountry = old('country', $user->country);
                @endphp

                @foreach($countries as $key => $value)
                    @php
                    $countryValue = is_string($key) ? $key : $value;
                    $countryLabel = $value;
                    @endphp
                    <option value="{{ $countryValue }}" {{ $selectedCountry == $countryValue ? 'selected' : '' }}>
                    {{ $countryLabel }}
                    </option>
                @endforeach
                </select>
            </div>
            @error('country')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="row mb-6 select2-primary">
            <label class="col-sm-3 col-form-label" for="multicol-language">Language</label>
            <div class="col-sm-9">
                @php
                $languages = ['en' => 'English', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese'];
                $selectedLanguages = old('language', $user->language ? explode(',', $user->language) : []);
                @endphp
                <select id="multicol-language" class="select2 form-select" name="language[]" multiple>
                @foreach($languages as $code => $name)
                    <option value="{{ $code }}" {{ in_array($code, $selectedLanguages) ? 'selected' : '' }}>
                    {{ $name }}
                    </option>
                @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-6">
          <label class="col-sm-3 col-form-label" for="multicol-birthdate">Birth Date</label>
          <div class="col-sm-9">
            <input type="text" id="flatpickr-date" class="form-control dob-picker" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}" placeholder="YYYY-MM-DD" />
            @error('date_of_birth')
              <p class="text-danger">{{ $errors->first('date_of_birth') }}</p>
            @enderror
          </div>
        </div>
        <div class="row">
          <label class="col-sm-3 col-form-label" for="multicol-phone">Phone No</label>
          <div class="col-sm-9">
            <input type="text" id="multicol-phone" class="form-control phone-mask" name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" placeholder="658 799 8941" aria-label="658 799 8941" />
            @error('mobile_number')
              <p class="text-danger">{{ $errors->first('mobile_number') }}</p>
            @enderror
          </div>
        </div>
        <div class="pt-6">
          <div class="row justify-content-end">
            <div class="col-sm-9">
              <button type="submit" class="btn btn-primary me-4">Submit</button>
              <button type="reset" class="btn btn-label-secondary">Cancel</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
  <!-- Form Label Alignment -->
@endsection