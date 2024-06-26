<?php


namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexOkBooking  extends JsonResource
{


    public function toArray(Request $request): array
    {

        $result = [];
        if ($this != null)
            if ($this != null)
            {
                $result = [
                    'id_booking' => $this['id']?? null,
                    'id_online_center' => $this['id_online_center']?? null,
                    'id_user' => $this['id_user']?? null,
                    'created_at' => date('yy_m_d', strtotime( $this['created_at'])) ?? null,
                    'name' => $this['users']['profile']['name']?? null,
                    'lastName' => $this['users']['profile']['lastName']?? null,
                    'birthDate' => $this['users']['profile']['birthDate']?? null,
                    'mobilePhone' => $this['users']['profile']['mobilePhone']?? null,
                    'specialization' => $this['users']['profile']['specialization']?? null,
                    'levelEducational' => $this['users']['profile']['levelEducational']?? null,
                    'id_profile' => $this['users']['profile']['id']?? null,
                    'id_coursepaper' => $this['bookingindex']['coursepaper'][0]['id']?? null,
                    'id_paper' => $this['bookingindex']['coursepaper'][0]['id_paper']?? null,
                    'id_bookingindex' => $this['bookingindex']['id']?? null,
                ];
            }


        return $result;
    }


}
