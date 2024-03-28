<?php

use App\Http\Controllers\ProfileController;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Mailer\Event\MessageEvent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // redirect to login page
    return redirect()->route('login');
    // return view('welcome');
});

Route::get('/dashboard', function () {
    // Get the last 10 days and number of messages sent at those days
    $messages = Message::selectRaw('DATE(scheduled_at) as date, COUNT(*) as value')
        ->where('created_at', '>=', now()->subDays(10))
        ->groupBy('date')
        ->get();
    foreach ($messages as $message) {
        $message->date = Carbon::parse($message->date)->format('d M, Y');
    }

    return view('dashboard', compact('messages'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// use scandir to get include files in routes/partials
$files = scandir(__DIR__ . '/partials');
foreach ($files as $file) {
    if (is_file(__DIR__ . '/partials/' . $file)) {
        require __DIR__ . '/partials/' . $file;
    }
}
