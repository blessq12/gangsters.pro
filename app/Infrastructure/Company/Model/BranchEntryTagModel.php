<?php

namespace App\Infrastructure\Company\Model;

use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tag_id
 * @property string $entry_kind
 * @property int $entry_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchEntryTagModel extends Model
{
    public $timestamps = false;

    protected $table = 'CMP_branch_entry_tags';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tag_id',
        'entry_kind',
        'entry_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tag_id' => 'integer',
        'entry_kind' => BranchMenuEntryKind::class,
        'entry_id' => 'integer',
    ];
}
