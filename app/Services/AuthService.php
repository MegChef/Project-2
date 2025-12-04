namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login($username, $password)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        if (!Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // You can generate Sanctum tokens here if needed:
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user'  => $user
        ];
    }
}
