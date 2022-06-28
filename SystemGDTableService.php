<?php
/**
 * Created by PhpStorm.
 * Use : 将数据记录生成图片表格
 * User: 鱼人Dr.代
 * Date: 2022-06-01
 * Time: 14:05
 */

namespace App\Services;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemGDTableService
{

    /**
     * 字体路径
     */
    private $str_font_path     = '/file/simsun.ttc';
    /**
     * 根路径
     */
    private $str_base_path     = '';
    /**
     * 图片路劲
     */
    private $str_file_path     = '/file/';
    /**
     * 图片全路径
     */
    private $str_save_path     = '';
    /**
     * 图片名称（实例化需写入扩展名）
     */
    private $str_file_name     = '';
    /**
     * 数据表标题
     */
    private $str_table_name    = '';
    /**
     * 数据表副标题
     */
    private $str_sub_table_name= '';
    /**
     * 数据表行数
     */
    private $int_table_row     = 1;
    /**
     * 数据表表头
     */
    private $arr_table_header  = [];
    /**
     * 数据表属性
     */
    private $arr_table_line    = [
        //数据表边像素
        'border'               => 20,
        //标题字体大小
        'title_font_size'      => 16,
        //副标题字体大小
        'sub_title_font_size'  => 12,
        //数据列字体大小
        'text_size'            => 12,
        //表头列字体大小
        'header_font_size'     => 12,
        //标题高度
        'title_height'         => 50,
        //副标题高度
        'sub_title_height'     => 30,
        //表头行高度
        'header_row_height'    => 40,
        //数据行高度
        'row_height'           => 35,
        //首列宽度
        'filed_first_width'    => 280,
        //数据列宽度
        'filed_data_width'     => 120,
        //数据列左偏移量
        'text_offset'          => [270,110,110,110,110],
        //表头列左偏移量
        'header_offset'        => [270,110,110,110,110],
    ];

    /**
     * 默认构造函数
     */
    public function __construct()
    {
        $this->str_base_path = rtrim(public_path(),'/');
        //字体路径
        $this->str_font_path = $this->str_base_path . $this->str_font_path;
        //图片名称
        $this->str_file_name = MD5(microtime()) . '_' . Carbon::now()->format('Ymd') . '.png';
    }

    /**
     * 构造函数
     */
    public function init($str_file_path,$str_file_name,$str_table_name,$arr_table_header,$str_sub_table_name,$arr_table_line)
    {
        $this->str_table_name     = $str_table_name;
        $this->arr_table_header   = $arr_table_header;
        $this->str_sub_table_name = $str_sub_table_name;
        if($str_file_path){
            $this->str_base_path  = '';
            $this->str_file_path  = $str_file_path;
        }
        if($str_file_name){
            $this->str_file_name  = $str_file_name;
        }
        if($arr_table_line){
            $this->arr_table_line = $arr_table_line;
        }

        return $this;
    }

    public function getFilePath()
    {
        return $this->str_file_path;
    }
    public function setFilePath($str_file_path)
    {
        $this->str_file_path = $str_file_path;
    }
    public function getFileName()
    {
        return $this->str_file_name;
    }
    public function setFileName($str_file_name)
    {
        $this->str_file_name = $str_file_name;
    }
    public function getTableName()
    {
        return $this->str_table_name;
    }
    public function setTableName($str_table_name)
    {
        $this->str_table_name = $str_table_name;
    }
    public function getSubTableName()
    {
        return $this->str_sub_table_name;
    }
    public function setSubTableName($str_sub_table_name)
    {
        $this->str_sub_table_name = $str_sub_table_name;
    }
    public function getTableRow()
    {
        return $this->int_table_row;
    }
    public function setTableRow($int_table_row)
    {
        $this->int_table_row = $int_table_row;
    }
    public function getTableHeader()
    {
        return $this->arr_table_header;
    }
    public function setTableHeader($arr_table_header)
    {
        $this->arr_table_header = $arr_table_header;
    }
    public function getTableLine($str_line_key = '')
    {
        if($str_line_key){
            return $this->arr_table_line[$str_line_key]??'';
        } else {
            return $this->arr_table_line;
        }
    }
    public function setTableLine($arr_table_line)
    {
        foreach ($arr_table_line as $line => $val){
            if(!isset($this->arr_table_line[$line])){
                continue;
            }
            $this->arr_table_line[$line] = $val;
        }
    }

    /**
     * 写入数据图片
     *
     * @param array $arr_table_data = [
                [
                'a'    => 'Frenzy Tile',
                'b'    => 14383,
                'c'    => 3355,
                .
                .
                .
                'thick'=> '线条像素点，默认 1',
                'bold' => '字体像素点，默认 1',
                ]
     *      ]
     * @return array ['绝对路径','相对路径']
     */
    public function run($arr_table_data)
    {
        //Table行数
        $this->int_table_row = count($arr_table_data);
        $int_column_num  = count($this->arr_table_header);

        //图片宽度
        $int_img_width   =
            $this->arr_table_line['filed_first_width'] +
            $this->arr_table_line['filed_data_width'] * ($int_column_num - 1) +
            $this->arr_table_line['border'] * 2;
        //图片高度
        $int_img_height  =
            $this->int_table_row * $this->arr_table_line['row_height'] +
            $this->arr_table_line['border'] * 2 +
            $this->arr_table_line['header_row_height'] +
            $this->arr_table_line['title_height'] +
            $this->arr_table_line['sub_title_height'];
        //表格顶部高度
        $int_border_top   =
            $this->arr_table_line['border'] +
            $this->arr_table_line['title_height'] +
            $this->arr_table_line['sub_title_height'];
        //表格底部高度
        $int_border_bottom= $int_img_height - $this->arr_table_line['border'];
        //表格列像素
        $arr_x_column = [];
        for($i = 0; $i < $int_column_num; $i++){
            if($i == 0){
                $arr_x_column[] =
                    $this->arr_table_line['border'] +
                    $this->arr_table_line['filed_first_width'];
            } else {
                $arr_x_column[] =
                    $this->arr_table_line['border'] +
                    $this->arr_table_line['filed_first_width'] +
                    $this->arr_table_line['filed_data_width'] * $i;
            }
        }

        //创建画布
        $obj_img         = imagecreatetruecolor($int_img_width, $int_img_height);
        //设定图片背景色
        $int_bg_color    = imagecolorallocate($obj_img, 255, 255, 190);
        //设定文字颜色
        $int_text_color  = imagecolorallocate($obj_img, 0, 0, 0);
        //设定边框颜色
        $int_border_color= imagecolorallocate($obj_img, 0, 0, 0);
        //设定边框颜色
        $int_white_color = imagecolorallocate($obj_img, 255, 255, 255);
        //填充图片背景色
        imagefill($obj_img, 0, 0, $int_bg_color);
        //画矩形:填充黑色背景
        imagefilledrectangle(
            $obj_img, $this->arr_table_line['border'],
            $this->arr_table_line['border'] + $this->arr_table_line['title_height'] + $this->arr_table_line['sub_title_height'],
            $int_img_width  - $this->arr_table_line['border'],
            $int_img_height - $this->arr_table_line['border'],
            $int_border_color
        );
        //画矩形:填充两个像素的外边框
        imagefilledrectangle(
            $obj_img,
            $this->arr_table_line['border'] + 2,
            $this->arr_table_line['border'] + $this->arr_table_line['title_height'] + $this->arr_table_line['sub_title_height'] + 2,
            $int_img_width  - $this->arr_table_line['border'] - 2,
            $int_img_height - $this->arr_table_line['border'] - 2,
            $int_bg_color
        );

        //画表格纵线 + 填充表头
        foreach($arr_x_column as $key => $x){
            $this->imageLineThick($obj_img, $x, $int_border_top, $x, $int_border_bottom,$int_border_color);
            //反复绘制加粗
            for($i=0; $i<3; $i++){
                imagettftext(
                    $obj_img, $this->arr_table_line['title_font_size'], 0,
                    $x - $this->arr_table_line['header_offset'][$key] + 1,
                    $int_border_top + $this->arr_table_line['header_row_height'] - 8,
                    $int_text_color, $this->str_font_path, $this->arr_table_header[$key]
                );
            }
        }
        //画表格横线 + 填充数据
        foreach($arr_table_data as $key => $item){
            $int_border_top += $this->arr_table_line['row_height'];
            //横线和字体像素点
            $int_bold = $int_thick = 1;
            if(isset($item['bold'])){ $int_bold = $item['bold']; unset($item['bold']);}
            if(isset($item['thick'])){ $int_thick = $item['thick']; unset($item['thick']); }
            $this->imageLineThick($obj_img, $this->arr_table_line['border'], $int_border_top,
                $int_img_width - $this->arr_table_line['border'],$int_border_top, $int_border_color,$int_thick
            );
            $sub = 0;
            foreach ($item as $value){
                for($i=0; $i<$int_bold; $i++){
                    imagettftext(
                        $obj_img, $this->arr_table_line['text_size'], 0,
                        $arr_x_column[$sub] - $this->arr_table_line['text_offset'][$sub],
                        $int_border_top + $this->arr_table_line['row_height'] - 10,
                        $int_text_color, $this->str_font_path, $value
                    );
                }
                $sub++;
            }
        }

        //计算标题写入起始位置
        $arr_title_box = imagettfbbox($this->arr_table_line['title_font_size'], 0, $this->str_font_path, $this->str_table_name);
        $int_box_width = $arr_title_box[2] - $arr_title_box[0];
        $int_box_height= $arr_title_box[1] - $arr_title_box[7];

        //写入标题
        imagettftext(
            $obj_img, $this->arr_table_line['title_font_size'], 0,
            ($int_img_width - $int_box_width) / 2, $this->arr_table_line['title_height'],
            $int_text_color, $this->str_font_path, $this->str_table_name
        );
        //写入副标题
        imagettftext($obj_img, $this->arr_table_line['sub_title_font_size'], 0,
            $this->arr_table_line['border'], $this->arr_table_line['title_height'] + $this->arr_table_line['sub_title_height'],
            $int_text_color, $this->str_font_path, $this->str_sub_table_name);

        //保存图片
        $this->str_save_path = $this->str_base_path . $this->str_file_path . $this->str_file_name;
        if(!is_dir($this->str_base_path . $this->str_file_path)){
            mkdir($this->str_base_path . $this->str_file_path,0777,true);
        }
        if(file_exists($this->str_save_path)){ $this->unlink(); }

        imagepng($obj_img,$this->str_save_path);

        return [$this->str_save_path,($this->str_file_path . $this->str_file_name)];
    }

    /**
     * 删除数据图片
     */
    public function unlink()
    {
        try{
            if(empty($this->str_save_path) || !file_exists($this->str_save_path)){
                return false;
            }
            unlink($this->str_save_path); return true;
        } catch (\Exception $e){
            Log::error('删除图片['.$this->str_save_path.']异常,' . $e->getMessage());
        }
        return false;
    }

    /**
     * 绘制粗线
     */
    private function imageLineThick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
    {
        if ($thick == 1) {
            return imageline($image, $x1, $y1, $x2, $y2, $color);
        }
        $t = $thick / 2 - 0.5;
        if ($x1 == $x2 || $y1 == $y2) {
            return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
        }
        $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
        $a = $t / sqrt(1 + pow($k, 2));
        $points = array(
            round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
            round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
            round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
            round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
        );
        imagefilledpolygon($image, $points, 4, $color);
        return imagepolygon($image, $points, 4, $color);
    }
}
