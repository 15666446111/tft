<?php

namespace App\Http\Requests;

/**
 * 聚合支付 商户进件类验证 
 */
class MerchantNetRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
            /**
             * @version  必填项  [<description>]
             */
            'merTypeRadio'      =>  'required|in:0,1,2',                //  商户类型 必须为0 1 2中的其中一个
            'MccCode'           =>  'required|exists:mccs,mcc',         //  商户MCC
            'regionCode'        =>  'required|exists:regions,area_code',//  地区编码
            'address'           =>  'required',                         //  详细地址
            
            // 法人信息
            'name'              =>  'required|between:2,6',             //  商户法人姓名
            'phone'             =>  'required|digits_between:11,11',    //  商户手机号 必填
            'idcardNo'          =>  [
                                        'required', 
                                        "unique:merchants,owner_cert_id,NULL,id,verfity_state,'1'",
                                        'regex:/(^\d{15}$)|(^\d{17}([0-9]|X)$)/'
                                    ],         //  法人身份证号
            'cardStartDate'     =>  'required|date_format:Y-m-d|before:tomorrow',       // 身份证有效期 起
            'cardEndDate'       =>  'required|date_format:Y-m-d|after:tomorrow',        // 身份证有效期 止

            'bankType'          =>  'required|in:0,2',      //  结算户类型
            'bankCardNo'        =>  'required',             //  银行账户
            'bankName'          =>  'required',             //  银行名称
            'bankAccName'       =>  'required|between:2,6', //  银行账户户名

        ];
    }

    /**
     * 获取已定义验证规则的错误消息。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'merTypeRadio.required'     =>  '请选择商户类型',
            'merTypeRadio.in'           =>  '商户类型不在可选范围内',

            'MccCode.required'          =>  '请选择商户行业',
            'MccCode.exists'            =>  '商户行业不存在',

            'regionCode.required'       =>  '请选择所在地区',
            'regionCode.exists'         =>  '所选地区不存在',

            'address.required'          =>  '请填写详细地址',

            'name.required'             =>  '请填写商户法人姓名',
            'name.between'              =>  '法人姓名不合法',

            'phone.required'            =>  '请上传法人手机号',
            'phone.digits_between'      =>  '手机号不合法',

            'idcardNo.required'         =>  '请填写法人证件号',
            'idcardNo.unique'           =>  '该身份证号已注册',
            'idcardNo.regex'            =>  '证件号不合法',

            'cardStartDate.required'    =>  '请选择有效期',
            'cardStartDate.date_format' =>  '有效期格式不正确',
            'cardStartDate.before'      =>  '证件不在有效期内',


            'cardEndDate.required'      =>  '请选择有效期',
            'cardEndDate.date_format'   =>  '有效期格式不正确',
            'cardEndDate.after'         =>  '证件不在有效期内',

            'bankType.required'         =>  '请选择结算户类型',
            'bankType.in'               =>  '结算户类型不在范围内',
            'bankCardNo.required'       =>  '请填写银行账号',
            'bankName.required'         =>  '请填写银行名称',
            'bankAccName.required'      =>  '请填写账号户名',
            'bankAccName.between'       =>  '户名不合法'
        ];
    }



}
