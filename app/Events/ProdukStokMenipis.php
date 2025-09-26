<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // WAJIB!
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProdukStokMenipis implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $nama_produk; // pakai nama_produk biar konsisten
    public $stok;

    public function __construct($nama_produk, $stok)
    {
        $this->nama_produk = $nama_produk;
        $this->stok = $stok;
    }

    public function broadcastOn()
    {
        return new Channel('stok-channel'); // nama channel
    }

    public function broadcastAs()
    {
        return 'stok-menipis'; // nama event
    }
}
