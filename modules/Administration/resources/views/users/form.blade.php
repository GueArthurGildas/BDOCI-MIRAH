<x-ui.form.input name="name" :label="__('administration::administration.users.fields.name')" :value="$user->name" required />
<x-ui.form.input name="email" type="email" :label="__('administration::administration.users.fields.email')" :value="$user->email" required />
<x-ui.form.select name="team_id" :label="__('administration::administration.users.fields.team')" :options="$teams" :value="$user->team_id" />
<x-ui.form.select name="role" :label="__('administration::administration.users.fields.role')" :options="App\Support\Authorization\Role::options()" :value="$user->roles->first()?->name" required />
<x-ui.form.input name="password" type="password" :label="__('administration::administration.users.fields.password')" autocomplete="new-password" :placeholder="$user->exists ? __('administration::administration.users.password_hint') : null" />
<x-ui.form.input name="password_confirmation" type="password" :label="__('administration::administration.users.fields.password_confirmation')" autocomplete="new-password" />
<x-ui.form.checkbox name="is_active" :label="__('administration::administration.users.fields.is_active')" :checked="$user->is_active" />
