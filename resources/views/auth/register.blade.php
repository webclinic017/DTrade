@extends('layouts.app')

@section('body')
    <el-col :lg="6" :sm="12" class="center">
        <el-card>
            <div slot="header">
                {{ __('Register') }}
            </div>
            <el-form method="POST" action="{{ route('register') }}">
                @csrf
                <el-form-item label="{{ __('Name') }}" class="{{ $errors->has('name') ? 'is-error' : '' }}">
                    <div class="el-input">
                        <input id="name" type="text" class="el-input__inner" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                    @if ($errors->has('name'))
                        <span class="el-form-item__error">{{ $errors->first('name') }}</span>
                    @endif
                </el-form-item>
                <el-form-item label="{{ __('E-Mail Address') }}" class="{{ $errors->has('email') ? 'is-error' : '' }}">
                    <div class="el-input">
                        <input id="email" type="email" class="el-input__inner" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    @if ($errors->has('email'))
                        <span class="el-form-item__error">{{ $errors->first('email') }}</span>
                    @endif
                </el-form-item>
                <el-form-item label="{{ __('Password') }}" class="{{ $errors->has('password') ? 'is-error' : '' }}">
                    <div class="el-input">
                        <input id="password" type="password" class="el-input__inner" name="password" required>
                    </div>
                    @if ($errors->has('password'))
                        <span class="el-form-item__error" role="alert">{{ $errors->first('password') }}</span>
                    @endif
                </el-form-item>
                <el-form-item label="{{ __('Confirm Password') }}">
                    <div class="el-input">
                        <input id="password_confirmation" type="password" class="el-input__inner" name="password_confirmation" required>
                    </div>
                </el-form-item>
                <button type="submit" class="el-button el-button--primary full-width">Register</button>
            </el-form>
        </el-card>
    </el-col>
@endsection
