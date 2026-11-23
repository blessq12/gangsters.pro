<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class BranchSetLineModel extends Model
{
    public $timestamps = false;

    protected $table = 'CMP_branch_set_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'set_id',
        'catalog_item_id',
        'quantity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'set_id' => 'integer',
        'catalog_item_id' => 'integer',
        'quantity' => 'integer',
    ];
}
