<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An outgoing expense — security guard wages, caretaker payments, utility
 * top-ups, repair invoices paid directly by the owner, etc.
 *
 * Maps the `expenses` table. Amount is KES, stored as DECIMAL(12,2).
 *
 * @property int $id
 * @property int $owner_id
 * @property string $amount
 * @property string $category  security | caretaker | maintenance | utilities | other
 * @property string|null $description
 * @property \Carbon\CarbonInterface $date
 * @property string|null $paid_to  person or business paid
 * @property string|null $receipt
 */
class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'expenses';

    public $timestamps = true;

    protected $fillable = [
        'amount',
        'category',
        'description',
        'date',
        'paid_to',
        'receipt',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
