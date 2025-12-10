<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Box extends Model
{
    protected $fillable = [
        'plant',
        'number_box',
        'number_box_alias',
        'type_box',
        'color_code_box',
        'status_box',
        'qr_code',
        'qr_number',
    ];

    const DEFAULTCOLOR = 'B01';

    const COLOR_CODES = [
        [
            'code_color' => 'B01',
            'color_name' => 'blue',
            'color' => '#0000FF',
        ],
        [
            'code_color' => 'R01',
            'color_name' => 'red',
            'color' => '#FF0000',
        ],
        [
            'code_color' => 'G01',
            'color_name' => 'green',
            'color' => '#00FF00',
        ],
        [
            'code_color' => 'C01',
            'color_name' => 'cream',
            'color' => '#fdff6cff',
        ],
        [
            'code_color' => 'J01',
            'color_name' => 'orange',
            'color' => '#f09e40ff',
        ],
        // Add more colors as needed
    ];

    const TYPE_BOXES = [
        [
            'code_box' => '6688',
            'name' => '6688 Rabbit box (1005 L x 335 W x 195 H)',
            'image' => 'https://rajaplastikindonesia.com/wp-content/uploads/2018/04/6688.jpg',
            'available_colors' => ['B01'],
        ],
        [
            'code_box' => 'TP391',
            'name' => 'TP391 Rabbit box (335x1005x103 mm)',
            'image' => 'https://www.ppcplastics.com/images/content/original-1409559967298.jpg',
            'available_colors' => ['B01'],
        ],
        [
            'code_box' => '6655',
            'name' => '6655 Rabbit box (670 L x 335 W x 195 H)',
            'image' => 'https://rabbit-plastics.com/po-content/uploads/6655.jpg',
            'available_colors' => ['B01']
        ],
        [
            'code_box' => '6653',
            'name' => '6653 Rabbit box (670 L x 335 W x 100 H)',
            'image' => 'https://www.rabbit-plastics.com/po-content/uploads/6653.jpg',
            'available_colors' => ['B01'],
        ],
        [
            'code_box' => '3324',
            'name' => '3324 Rabbit box (600 L x 400 W x 250 J)',
            'image' => 'https://down-id.img.susercontent.com/file/9f2635fa591d5c6d144ad878909212b2.webp',
            'available_colors' => ['B01'],
        ],
        [
            'code_box' => '7006',
            'name' => '7006 Rabbit box (P840 x L630 x T375 mm)',
            'image' => 'https://static.wixstatic.com/media/db018d_5718eba64dfb48a9874e66b710e449a8~mv2.jpg/v1/fill/w_600,h_600,al_c,lg_1,q_85/db018d_5718eba64dfb48a9874e66b710e449a8~mv2.jpg',
            'available_colors' => ['B01'],
        ],
        [
            'code_box' => '4066',
            'name' => '4066 Rabbit box (37.5 x 32 x 16.5 cm)',
            'image' => 'https://static.wixstatic.com/media/1648dd_faca01364e484a05a8555921fc7d0141~mv2.jpg/v1/fill/w_497,h_497,al_c,lg_1,q_80,enc_auto/1648dd_faca01364e484a05a8555921fc7d0141~mv2.jpg',
            'available_colors' => ['B01'],
        ],

    ];

    public static function getColorCodes($codeColor = null)
    {
        if ($codeColor) {
            return array_filter(self::COLOR_CODES, function ($color) use ($codeColor) {
                return $color['code_color'] === $codeColor;
            });
        }
        return self::COLOR_CODES;
    }

    public static function getColorData($codeColor)
    {
        $colorData = self::getColorCodes($codeColor);
        return count($colorData) ? array_values($colorData)[0] : null;
    }

    public static function getTypeBoxes()
    {
        return self::TYPE_BOXES;
    }

    public function getLastStatusBox()
    {
        return TrackingBox::where('number_box', $this->number_box)
            ->orderBy('date_time', 'desc')
            ->first();
    }

    public function getTimelineBox()
    {
        $timeline = [];

        // Add box registration info
        $timeline[] = [
            'event' => 'Box Registered',
            'date_time' => $this->created_at->toDateTimeString(),
            'details' => [
                'number_box' => $this->number_box,
                'type_box' => $this->type_box,
                'status_box' => $this->status_box,
            ],
        ];

        // Add tracking info
        $trackingBoxes = TrackingBox::where('number_box', $this->number_box)
            ->orderBy('date_time', 'asc')
            ->get();

        foreach ($trackingBoxes as $tracking) {
            $timeline[] = [
                'event' => 'Tracking Update',
                'date_time' => $tracking->date_time,
                'details' => [
                    'status' => $tracking->status,
                    'dn_number' => $tracking->dn_number,
                    'kanban' => $tracking->kanban,
                    'destination_code' => $tracking->destination_code,
                    'scanned_by' => $tracking->scanned_by,
                ],
            ];
        }

        return $timeline;
    }

    public static function countBoxesByType()
    {
        return self::groupBy('type_box')
            ->selectRaw('type_box, COUNT(*) as total')
            ->get();
    }
}
