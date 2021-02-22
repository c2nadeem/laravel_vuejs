<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BGQuestionsResource;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\BackgroundCheck\BackgroundCheckModel;
use App\Models\BackgroundCheck\BackgroundCheckAnswersModel;
use App\Models\BackgroundCheck\BackgroundCheckQuestionsModel;
use App\Models\ccInfo\ccInfoModel;
use App\Models\Access\User\User;
use App\Helpers\Equifax;

class OnlineBackgroundChecksController extends APIController
{
    protected $repository;
    protected $user;
    /**
     * __construct.
     *
     * @param $repository
     */
    public function get_questions()
    {
        $resp = [];
        $questions = BackgroundCheckQuestionsModel::get();
        $ret['message'] = "success";
        $ret['status'] = true;
        $question_arr = [];
        if ($questions) {
            foreach ($questions as $k => $q) {
                // check if item is already selected (if user has filled fields previously.)
                $q->selected_value = '';
                $question_arr[$q->field_id] = $q;
            }
        }
        $ret['result'] = $question_arr;
        return $ret;
    }
    public function if_has_unfinised_background_check(Request $request)
    {
        $record = BackgroundCheckModel::where('user_id', Auth::user()->id)->where('status', 0)->orderBy('id', 'desc')->first();
        if ($record) {
            $questions = BackgroundCheckQuestionsModel::get();
            $question_arr = [];
            if ($questions) {
                foreach ($questions as $k => $q) {
                    // check if item is already selected (if user has filled fields previously.)
                    $if_ans  = BackgroundCheckAnswersModel::where('question_id', $q->id)->where('bg_check_id', $record->id)->first();
                    if ($if_ans) {
                        if ($q->field_id == 'front_picture_of_id_1' && !empty($if_ans->answer)) {
                            $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                        } else if ($q->field_id == 'back_picture_of_id_1' && !empty($if_ans->answer)) {
                            $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                        } else if ($q->field_id == 'record_short_video' && !empty($if_ans->answer)) {
                            $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                        } else if ($q->field_id == 'certificate_file' && !empty($if_ans->answer)) {
                            $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                        } else if ($q->field_id == 'convictions' && !empty($if_ans->answer)) {
                            $q->selected_value = @unserialize($if_ans->answer);
                        } else {
                            $q->selected_value = ($if_ans->answer == 1) ? true : $if_ans->answer;
                        }
                    } else {
                        $q->selected_value = '';
                    }
                    $question_arr[$q->field_id] = $q;
                }
            }
            return ['response' => true, 'record' => $record, 'questions' => $question_arr];
        } else {
            return ['response' => true, 'record' => null];
        }
    }
    public function get_background_check_by_id(Request $request)
    {
        $id = $request->id;
        $user = Auth::user();
        $is_policeman = check_if_user_is_policemen($user);
        if ($is_policeman) {
            $record = BackgroundCheckModel::where('id', $id)->where('status', 1)->orderBy('id', 'desc')->first();
            $trict = (isset($request->strict) && $request->strict == true) ? true : false;
            if (!$record && $trict == false) {
                $record = BackgroundCheckModel::where('status', 1)
                    ->where('police_check_step', 1)
                    ->where(function ($query) {
                        $query->where('police_check_status', 0)->orWhere('police_check_status', 1);
                    })
                    ->orderBy('id', 'asc')->first();
            }
            if ($record) {


                if ($record->police_check_status > 0 && $record->police_check_step > 1) {
                    $msg = ($record->police_check_status == 1) ? 'Record already has been accepted.' : 'Record already has been rejected.';
                    return ['status' => false, 'message' => $msg];
                }
                if ($record->equifax_reasons != null) {
                    $record->equifax_reasons = unserialize($record->equifax_reasons);
                    $reason_arr = [];
                    if (count($record->equifax_reasons)) {
                        $i = 0;
                        foreach ($record->equifax_reasons as $reas) {
                            if (is_array($reas['reason'])) {
                                foreach ($reas['reason'] as $rk => $rs) {
                                    $reason_arr[$i]['reason'] = $rs;
                                    $reason_arr[$i]['reason_code'] = $reas['reason_code'][$rk];
                                    $i++;
                                }
                            } else {
                                $reason_arr[$i]['reason'] = $reas['reason'];
                                $reason_arr[$i]['reason_code'] = $reas['reason_code'];
                                $i++;
                            }
                        }
                    }
                    $record->equifax_reasons = $reason_arr;
                    //print_r($record->equifax_reasons);

                }
                $question_arr = $this->get_record_and_ans_by_id($record);
                // send inprogress notification to customer
                if ($record->check_inprogress == 0) {
                    $customer = User::find($record->user_id);
                    if ($customer) {
                        $pats = [
                            'first_name' => $customer->first_name,
                            'last_name' => $customer->last_name,
                            'order_type' => get_check_type($question_arr['bg_check_type']->selected_value),
                            'order_id' => $record->id,
                        ];
                        // send `Z`
                        $to = $customer->email; // customer
                        $data = [
                            'to' => $to,
                        ];
                        $resp = prepare_postmark_email($template_id = 35, $pats, $data);
                        $rc = BackgroundCheckModel::where('id', $id)->first();
                        $rc->check_inprogress = 1;
                        $rc->save();
                    }
                }

                return ['status' => true, 'record' => $record, 'questions' => $question_arr];
            } else {
                return ['status' => false, 'message' => 'Record not found.'];
            }
        } else {
            return ['status' => false, 'message' => 'Permission denied.'];
        }
    }
    public function get_record_and_ans_by_id($record)
    {
        $questions = BackgroundCheckQuestionsModel::get();
        $question_arr = [];
        if ($questions) {
            foreach ($questions as $k => $q) {
                // check if item is already selected (if user has filled fields previously.)
                $if_ans  = BackgroundCheckAnswersModel::where('question_id', $q->id)->where('bg_check_id', $record->id)->first();
                if ($if_ans) {
                    if ($q->field_id == 'front_picture_of_id_1' && !empty($if_ans->answer)) {
                        $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                    } else if ($q->field_id == 'back_picture_of_id_1' && !empty($if_ans->answer)) {
                        $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                    } else if ($q->field_id == 'record_short_video' && !empty($if_ans->answer)) {
                        $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                    } else if ($q->field_id == 'certificate_file' && !empty($if_ans->answer)) {
                        $q->selected_value = $this->get_S3_file_link($if_ans->answer);
                    } else if ($q->field_id == 'convictions' && !empty($if_ans->answer)) {
                        $q->selected_value = @unserialize($if_ans->answer);
                    } else {
                        $q->selected_value = ($if_ans->answer == 1) ? true : $if_ans->answer;
                    }
                } else {
                    $q->selected_value = '';
                }
                $question_arr[$q->field_id] = $q;
            }
        }
        return $question_arr;
    }
    public function get_background_check_by_id_for_admin(Request $request)
    {
        $id = $request->id;
        $user = Auth::user();
        $is_admin = is_super_admin($user);
        if ($is_admin) {
            $record = BackgroundCheckModel::where('id', $id)->orderBy('id', 'desc')->first();
            if ($record) {
                if ($record->equifax_reasons != null) {
                    $record->equifax_reasons = unserialize($record->equifax_reasons);
                }
                $question_arr = $this->get_record_and_ans_by_id($record);
                return ['status' => true, 'record' => $record, 'questions' => $question_arr];
            } else {
                return ['status' => false, 'message' => 'Record not found.'];
            }
        } else {
            return ['status' => false, 'message' => 'Permission denied.'];
        }
    }
    public function get_records_to_be_verified(Request $request)
    {
        $id = $request->id;
        $user = Auth::user();
        $is_policeman = check_if_user_is_policemen($user);
        if ($is_policeman) {
            $records = BackgroundCheckModel::where('police_check_status', 0)->where('status', 1)->orderBy('id', 'desc')->get();
            $ret_arr = [];
            if ($records) {
                foreach ($records as $k => $rc) {
                    $rc->user = User::find($rc->user_id);
                    $records[$k] = $rc;
                }
                return ['status' => true, 'records' => $records];
            } else {
                return ['status' => false, 'message' => 'Record not found.'];
            }
        } else {
            return ['status' => false, 'message' => 'Permission denied.'];
        }
    }
    public function get_background_check_list(Request $request)
    {
        $records = BackgroundCheckModel::orderBy('id', 'desc')->get();
        if ($records) {
            foreach ($records as $k => $rc) {
                $rc->user = User::find($rc->user_id);
                $records[$k]->name = $rc->user->first_name . ' ' . $rc->user->last_name;
                $records[$k]->email = $rc->user->email;
            }
            return ['status' => true, 'records' => $records];
        } else {
            return ['status' => false, 'message' => 'Record not found.'];
        }
    }
    public function accept_or_reject_background_check(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $user = Auth::user();
        $is_policeman = check_if_user_is_policemen($user);
        if ($is_policeman) {
            $record = BackgroundCheckModel::where('id', $id)->where('status', 1)->orderBy('id', 'desc')->first();
            if ($record) {
                $record->police_check_status = ($status == 1) ? 1 : 2;
                $record->save();
                // send email to customer.
                $customer = User::find($record->user_id);
                if ($customer) {
                    $pats = [
                        'full_name' => $customer->first_name . ' ' . $customer->last_name,
                    ];
                    $to = $customer->email; // customer
                    $data = [
                        'to' => $to,
                    ];
                    $template_id = ($status == 1) ? 37 : 36;
                    $resp = prepare_postmark_email($template_id, $pats, $data);
                }
                return ['status' => true, 'message' => 'Record status has been updated.'];
            } else {
                return ['status' => false, 'message' => 'Record not found.'];
            }
        } else {
            return ['status' => false, 'message' => 'Permission denied.'];
        }
    }
    public function get_unfinised_background_check(Request $request)
    {

        $record = BackgroundCheckModel::where('user_id', Auth::user()->id)->where('status', 0)->orderBy('id', 'desc')->first();
        if ($record) {
            return ['response' => true, 'record' => $record];
        } else {
            return ['response' => true, 'record' => null];
        }
    }
    function get_S3_file_link($file_name)
    {
        $url = Storage::disk('s3')->temporaryUrl(
            $file_name,
            now()->addMinutes(15),
        );
        return $url;
    }
    public function upload_background_check_step_file(Request $request)
    {
        $id = $request->id;
        $question_id = $request->question_id;
        $record = BackgroundCheckModel::find($id);
        if ($record) {
            if ($request->hasFile('file_value')) {
                $file =  $request->file('file_value');
                if ($request->file_name == 'record_short_video') {
                    $rules = array(
                        'file_value'       => 'mimes:mp4,mov,ogg,qt,avi,divx',
                    );
                    $validator = Validator::make($request->all(), $rules);
                    // process the login
                    if ($validator->fails()) {

                        return ['status' => false, 'message' => 'Please provide a valid video format.'];
                    }
                    $current_step = ($request->last_completed_step == 8) ? 9 : $request->last_completed_step;

                    $record->current_step = $current_step;
                    $record->save();
                } else if ($request->file_name == 'front_picture_of_id_1' || $request->file_name == 'back_picture_of_id_1') {
                    $rules = array(
                        'file_value'       => 'mimes:jpeg,jpg,png,gif|required',
                    );
                    $validator = Validator::make($request->all(), $rules);
                    // process the login
                    if ($validator->fails()) {
                        return ['response' => false, 'message' => 'Please provide a valid Image format.'];
                    }
                }
                $file_name = $this->_upload_document_to_s3($file, $record->id);
                $url = $this->get_S3_file_link($file_name);

                $if_ans = BackgroundCheckAnswersModel::where('question_id', $question_id)->first();
                if ($if_ans) {
                    $ans = $if_ans;
                } else {
                    $ans = new BackgroundCheckAnswersModel();
                }
                $ans->question_id = $question_id; // question id
                $ans->bg_check_id = $record->id;
                $ans->answer = $file_name;
                $ans->save();
                return ['response'  => true, 'message' => 'File has been uploaded successfully.', 'url' => $url];
            } else {

                return ['response'  => false, 'message' => 'Please provide a valid file format.'];
            }
        }
        return ['response'  => false, 'message' => 'Invalid request.'];
    }
    public function upload_background_check_certificat_file_and_complete(Request $request)
    {
        $id = $request->id;
        $question_id = $request->question_id;
        $record = BackgroundCheckModel::find($id);
        $certificate_link = '';
        if ($record) {
            if ($request->hasFile('file_value')) {
                $file =  $request->file('file_value');
                $file_type  = $file->getMimeType();
                $file_name = $this->_upload_document_to_s3($file, $record->id);
                if ($request->file_name == 'certificate_file') {
                    $record->certificate_file = $file_name;
                }
                $if_ans = BackgroundCheckAnswersModel::where('question_id', $question_id)->first();
                if ($if_ans) {
                    $ans = $if_ans;
                } else {
                    $ans = new BackgroundCheckAnswersModel();
                }
                $ans->question_id = $question_id; // question id
                $ans->bg_check_id = $record->id;
                $ans->answer = $file_name;
                $certificate_download_hash = md5(time() . '_' . $record->id);
                $record->certificate_download_hash = $certificate_download_hash;
                $record->certificate_file_type = $file_type;
                $certificate_link = 'Download Ceriticate: <a href="' . url('/certificate/' . $record->user_id . '/' . $certificate_download_hash) . '">' . url('/certificate/' . $record->user_id . '/' . $certificate_download_hash) . '</a>';
                $ans->save();
            }
            $police_check_status = 2;
            $record->check_result = $request->check_result;
            $record->police_check_status = $police_check_status;
            $record->save();
            // sned notification
            $customer = User::find($record->user_id);

            $pats = [
                'order_id' => $record->id,
                'order_type' => '',
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'background_result' => $record->check_result,
                'background_message' => order_check_result_by_police($record->check_result),
                'certificate_url' => $certificate_link,
            ];
            // send `Z`
            $to = $customer->email; // customer
            $data = [
                'to' => $to,
            ];
            $template_id = 36;

            $resp = prepare_postmark_email($template_id, $pats, $data);

            return ['response'  => true, 'message' => 'Record has been uploaded successfully.', 'resp' => $resp];
        }
        return ['response'  => false, 'message' => 'Invalid request.'];
    }
    public function download_certificate(Request $request, $id, $hash)
    {
        $record = BackgroundCheckModel::where('certificate_download_hash', $hash)->where('user_id', $id)->first();
        if ($record) {
            $name = $record->certificate_file;

            $assetPath = Storage::disk('s3')->url($name);
            $url = Storage::disk('s3')->temporaryUrl(
                $name,
                now()->addMinutes(15),
                ['ResponseContentType' => 'application/octet-stream']
            );
            return redirect($url);
        }
    }
    public function background_check_update_step_count(Request $request)
    {

        $id = $request->id;
        $current_step = $request->current_step;
        $record = BackgroundCheckModel::find($id);
        if ($record) {
            $record->current_step = $current_step;
            $record->save();
            return ['response'  => true, 'message' => 'Step has been updated'];
        }
        return ['response'  => false, 'message' => 'Invalid request.'];
    }
    public function update_convicted_crime(Request $request)
    {

        $id = $request->id;
        $record = BackgroundCheckModel::find($id);
        if ($record) {

            $record->convicted_crime = $request->status;
            $record->save();
            return ['response'  => true, 'message' => 'Step has been updated'];
        }
        return ['response'  => false, 'message' => 'Invalid request.'];
    }
    public function make_background_check_payment(Request $request)
    {
        $user = Auth::user();
        $id = $request->id;
        $record = BackgroundCheckModel::find($id);
        if ($record) {
            $profile_id = 0;
            if (is_bg_user($user)) {
                $exp_date = str_replace('/', '', $request->get('exp_date'));
                list($expiry_month, $expiry_year) = str_split($exp_date, 2);
                // create profile first
                $profile_data = [
                    'billing' => [
                        'name' => $request->get('name_on_card'),
                        'email_address' => $user->email,
                        'phone_number' => '519-955-1234',
                        'address_line1' => $request->get('street_address'),
                        'city' => 'Goderich',
                        'province' => $request->get('province'),
                        'postal_code' => $request->get('postal'),
                        'country' => $request->get('country')
                    ]
                ];

                $card_data = [
                    'card' => [
                        'name' => $request->get('name_on_card'),
                        'number' => (!empty($request->get('cc_number'))) ? $request->get('cc_number') : '4030000010001234',
                        'expiry_month' => $expiry_month,
                        'expiry_year' => $expiry_year,
                        'cvd' => $request->get('cvd')
                    ]
                ];

                $result = create_payment_profile($profile_data, $card_data);
                if ($result['status'] == true) {
                    $profile_id = $result['profile_id'];
                    $user->beanstream_profile_id = $profile_id;
                    $cc_last_4 = substr($request->get('cc_number'), -7);
                    if ($result['card_added'] == true) {
                        $cc = new ccInfoModel();
                        $cc->user_id = $user->id;
                        $cc->name_on_card = $request->get('name_on_card');
                        $cc->cc_last_4_digit = $cc_last_4;
                        $cc->expiry = $exp_date;
                        $cc->cvv = $request->get('cvd');
                        $cc->save();
                    }
                    $user->save();
                    if (!empty($profile_id)) {
                        // check if profile has cards
                        $result = get_profile_card($profile_id);
                        if ($result['status'] == true) {
                            $order_number = bin2hex(random_bytes(8));
                            $card_id = 1;
                            $complete = true;
                            // make total amount 
                            $to_be_shipped = $request->want_bg_check_copy;
                            $shipping_amount = $request->shipping_amount;
                            $shipping_address = $request->shipping_address;
                            $shipping_to = $request->shipping_to;
                            //bg_check_type.selected_value

                            $record_with_questions = $this->get_record_and_ans_by_id($record);
                            $amount = bg_check_total($to_be_shipped, $shipping_amount, $record_with_questions['bg_check_type']->selected_value);
                            // amount array.
                            $profile_payment_data = array(
                                'order_number' => $order_number,
                                'amount' => $amount
                            );
                            $result = make_profile_payment($profile_id, $card_id, $profile_payment_data, $complete);
                            if ($result['status'] == true) {
                                // payment was successfull, now create order
                                $record->total = $amount;
                                $record->to_be_shipped = $to_be_shipped;
                                $record->shipping_address = $shipping_address;
                                $record->shipping_to = $shipping_to;
                                $record->payment_response = serialize($result);
                                $record->payment_status = 1;
                                $record->status = 1;
                                $record->save();
                                // send to customer
                                $order_type = $record_with_questions['bg_check_type']->selected_value;
                                $phone = $record_with_questions['phone_number']->selected_value;
                                $subtotal = bg_check_subtotal($to_be_shipped, $shipping_amount, $order_type);
                                $tax_amount = bg_check_taxed_amount($subtotal);

                                $pats = [
                                    'first_name' => $user->first_name,
                                    'last_name' => $user->last_name,
                                    'order_id' => $record->id,
                                    'order_type' => get_check_type($order_type),
                                    'delivery_method' => get_shipping_method($request->shipping_method),
                                    'subtotal' => $subtotal,
                                    'total' => $amount,
                                    'tax' => $tax_amount,
                                    'payment_method' => 'Credit Card',
                                    'payment_date' => date('F j, Y'),
                                    'transaction_id' => $result['response']['id'],
                                ];
                                // send `Z`
                                $to = $user->email; // customer
                                $data = [
                                    'to' => $to,
                                ];
                                $resp = prepare_postmark_email($template_id = 33, $pats, $data);
                                // send to Globeia

                                // send `Z`
                                $to = get_admin_email(); // to globeia admin
                                $data = [
                                    'to' => $to,
                                ];
                                $resp = prepare_postmark_email($template_id = 33, $pats, $data);
                                // send to policemane

                                // send in progress email to customer
                                $link = url('/#/online-background-check');
                                $pats = [
                                    'order_id' => $record->id,
                                    'order_type' => get_check_type($order_type),
                                    'link' => $link,
                                    'first_name' => $user->first_name,
                                    'last_name' => $user->last_name,
                                    'recipient_name' => $shipping_to,
                                    'client_email' => $user->email,
                                    'client_phone' => $phone,
                                    'shipping_address' => $shipping_address,

                                ];
                                $to = get_police_email(); // police
                                $data = [
                                    'to' => $to,
                                ];
                                $resp = prepare_postmark_email($template_id = 34, $pats, $data);
                                $to = get_admin_email(); // admin
                                $data = [
                                    'to' => $to,
                                ];
                                $resp = prepare_postmark_email($template_id = 38, $pats, $data);

                                return ['status' => true, 'message' => 'We have received your request. We will contact back soon.'];
                            } else {
                                // return bambora error
                                return $result;
                            }
                        } else {
                            // return bambora error
                            return $result;
                        }
                    }
                } else {
                    $result['response'] = false;
                    if ($result['code'] == 19) {
                        $result['message'] = 'Please make sure all fields have been filled correctly below.';
                    }
                    return $result;
                }
            }
        } else {
            return ['status' => false, 'message' => 'Invalid request'];
        }
    }
    /**
     * uploads file to Amazon S3
     */
    public function post_equifax_question(Request $request)
    {
        $current_step = $request->last_completed_step;
        $id = $request->record_id;
        $record = BackgroundCheckModel::where('id', $id)->first();
        if ($record) {
            $answers = $request->answers;
            $cquifax_transaction_id = $request->equifax_transaction_id;
            $cnteractive_query_id = $request->interactive_query_id;
            $params = [
                'IQAnswerRequest' => [],
                'transactionKey' => $cquifax_transaction_id,
                'interactiveQueryId' => $cnteractive_query_id,
            ];
            if (count($answers)) {
                foreach ($answers as $k => $str) {
                    list($questionId, $answerId) = explode('_', $str);
                    $params['IQAnswerRequest'][] = [
                        'Answer' => [
                            'questionId' => $questionId,
                            'answerId' => $answerId,
                        ]
                    ];
                }
            }

            $equifax = equifax_info();
            list($wsdl, $username, $password) = array_values($equifax);
            $client = new \SoapClient($wsdl, array('trace' => 1));
            $client->__setSoapHeaders(array(new Equifax($username, $password)));
            $resp = $client->__soapCall('interactiveQueryResponse', array('parameters' => $params));
            if (isset($resp->AssesmentComplete)) {
                $response = $resp->AssesmentComplete;
                $equifax_reasons = $response->ReasonCode;
                $reasons = [];
                if (count($equifax_reasons)) {
                    foreach ($equifax_reasons as $k => $reason) {
                        $reasons[] = [
                            'reason' => $reason->description,
                            'reason_code' => $reason->_
                        ];
                    }
                }
            }
            $record->equifax_reasons = serialize($reasons);
            $record->equifax_status = 1;
            $record->equifax_response = serialize($resp);
            $record->save();
            $ret = [
                'response' => true,
                'message' => 'background check has been validated.',
            ];
        } else {
            $ret = [
                'response' => false,
                'message' => 'Invalid request',
            ];
        }
        return $ret;
    }
    function _upload_document_to_s3($file, $record_id)
    {
        $type  = $file->getMimeType();
        $file_name  = 'bg_check_' . $record_id . '_' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs(
            '',
            $file_name,
            's3'
        );
        return $path;
    }
    public function update_background_check_step_count_only(Request $request)
    {
        $ret = [];
        $ret['response'] = false;
        $ret['error_message'] = 'Invalid request.';
        $id = $request->id;
        $user = Auth::user();
        $record = BackgroundCheckModel::where('id', $id)->where('user_id', $user->id)->first();
        if ($record) {
            $record->current_step = $request->current_step;
            if ($request->has('convicted_crime')) {
                $record->convicted_crime = ($request->convicted_crime == true) ? 1 : 0;
                $ret['crime'] = $record->convicted_crime;
            }
            $record->save();
            $ret['response'] = true;
            $ret['error_message'] = 'Step updated.';
        }
        return $ret;
    }
    public function update_background_check_step(Request $request)
    {
        $ret = [];
        $ret['response'] = false;
        $ret['error_message'] = 'Invalid request.';
        $user = Auth::user();
        $id = $request->id;
        $current_step = $request->last_completed_step;
        if ($id > 0) {
            $record = BackgroundCheckModel::where('id', $id)->first();
        } else {
            $record = new BackgroundCheckModel();
            $record->user_id = $user->id;
            $record->order_id = 0;
            $record->status = 0;
            $record->save();
        }
        $record->current_step = $current_step;
        $record->save();
        $step = $request->current_step;
        $form = $request->form;
        if (count($form)) {
            foreach ($form as $k => $rec) {
                // check and update if user has already answered.
                $if_ans = BackgroundCheckAnswersModel::where('question_id', $rec['id'])->first();
                if ($if_ans) {
                    $ans = $if_ans;
                } else {
                    $ans = new BackgroundCheckAnswersModel();
                }
                $ans->question_id = $rec['id']; // question id
                $ans->bg_check_id = $record->id;
                if ($step == 11 && in_array('convictions', $request->fields)) {

                    $ans->answer = serialize($rec['selected_value']);
                } else {
                    $ans->answer = $rec['selected_value'];
                }
                $ans->save();
            }
        }
        if ($step == 5) {
            // make equifax request to validate;
            $equifax = equifax_info();
            list($wsdl, $username, $password) = array_values($equifax);
            $client = new \SoapClient($wsdl, array('trace' => 1));
            $client->__setSoapHeaders(array(new Equifax($username, $password)));
            $equifax_fields = make_equifax_fields($request->form);
            extract($equifax_fields);
            list($yyyy, $mm, $dd) = explode('-', $date_of_birth_name);
            $params = [
                'Identity' => [
                    'Name' => [
                        'FirstName' => $legal_first_name,
                        'MiddleName' => $middle_name,
                        'LastName' => $legal_lasst_name,
                    ],
                    'Address' => [
                        'addressType' => 'Current',
                        'HybridAddress' => [
                            'AddressLine' => $current_street_address,
                            'City' => $current_city,
                            'Province' => $current_provice,
                            'PostalCode' => $current_postal,
                        ]
                    ],
                    'SIN' => '719029506',
                    'DateOfBirth' => [
                        'Day' => $dd,
                        'Month' => $mm,
                        'Year' => $yyyy,
                    ],
                ],
                'ProcessingOptions'  => [
                    'Language' => 'English',
                    'EnvironmentOverride' => 'uat'
                ]

            ];
            $resp = $client->__soapCall('startTransaction', array('parameters' => $params));
            $equifax_resp = [];
            if (isset($resp->FieldChecksFailed)) {
                $equifax_resp['response'] = true;
                $equifax_resp['check_status'] = false;
                $equifax_resp['reason'] = isset($resp->FieldChecksFailed->FieldInErrorXPath) ? $resp->FieldChecksFailed->FieldInErrorXPath : 'background check failed.';
                $equifax_resp['equifax_transaction_id'] = $resp->transactionKey;
                return $equifax_resp;
            } else if (isset($resp->AssesmentComplete)) {
                // mean verification failed.
                $reason = '';
                $reason_code = 0;

                if (isset($resp->AssesmentComplete->ReasonCode)) {
                    if (is_array($resp->AssesmentComplete->ReasonCode)) {
                        $ReasonCodes = $resp->AssesmentComplete->ReasonCode;
                        $reason_code = [];
                        $reason = [];
                        foreach ($ReasonCodes as $res_k => $res_v) {
                            $reason[] = $res_v->description;
                            $reason_code[] = $res_v->_;
                        }
                    } else {
                        $reason_code = $resp->AssesmentComplete->ReasonCode->_;
                        $reason = $resp->AssesmentComplete->ReasonCode->description;
                    }
                }
                $equifax_transaction_id = isset($resp->transactionKey) ? $resp->transactionKey : 0;
                $equifax_resp['response'] = true;
                $equifax_resp['check_status'] = false;
                $equifax_resp['reason'] = is_array($reason) ? implode('<br>', $reason) : $reason;
                $equifax_resp['reason_code'] = is_array($reason_code) ? implode('<br>', $reason_code) : $reason_code;
                $equifax_resp['equifax_transaction_id'] = $equifax_transaction_id;
                // update record equifax status
                $record->equifax_status = 0;
                $record->equifax_response = serialize($resp);
                $record->equifax_reasons = serialize([['reason' => $reason, 'reason_code' => $reason_code]]);
                $record->equifax_transaction_id = isset($resp->transactionKey) ? $resp->transactionKey : 0;
                $record->save();
                return $equifax_resp;
            } else if (isset($resp->ApplicationVerification)) {
                // mean verification failed.
                $reason = '';
                $reason_code = 0;

                if (isset($resp->ApplicationVerification->ReasonCode)) {
                    if (is_array($resp->ApplicationVerification->ReasonCode)) {
                        $ReasonCodes = $resp->ApplicationVerification->ReasonCode;
                        $reason_code = [];
                        $reason = [];
                        foreach ($ReasonCodes as $res_k => $res_v) {
                            $reason[] = $res_v->description;
                            $reason_code[] = $res_v->_;
                        }
                    } else {
                        $reason_code = $resp->ApplicationVerification->ReasonCode->_;
                        $reason = $resp->ApplicationVerification->ReasonCode->description;
                    }
                }
                $equifax_transaction_id = isset($resp->transactionKey) ? $resp->transactionKey : 0;
                $equifax_resp['response'] = true;
                $equifax_resp['check_status'] = false;
                $equifax_resp['reason'] = is_array($reason) ? implode('<br>', $reason) : $reason;
                $equifax_resp['reason_code'] = is_array($reason_code) ? implode('<br>', $reason_code) : $reason_code;
                $equifax_resp['equifax_transaction_id'] = $equifax_transaction_id;
                // update record equifax status
                $record->equifax_status = 0;
                $record->equifax_response = serialize($resp);
                $record->equifax_reasons = serialize([['reason' => $reason, 'reason_code' => $reason_code]]);
                $record->equifax_transaction_id = isset($resp->transactionKey) ? $resp->transactionKey : 0;
                $record->save();
                return $equifax_resp;
            } else if (isset($resp->InteractiveQuery)) {
                $questions = [];
                $interactiveQueryId = -1;
                $TransactionKey = 0;
                if (isset($resp->transactionKey)) {
                    $TransactionKey = $resp->transactionKey;
                }
                if ($TransactionKey > 0) {

                    if (isset($resp->InteractiveQuery->Question)) {

                        $answers = [];
                        $questions = $resp->InteractiveQuery->Question;
                        $interactiveQueryId = $resp->InteractiveQuery->interactiveQueryId;

                        $equifax_questions = [];
                        foreach ($questions as $i => $que_obj) {
                            $question = $que_obj->QuestionText;
                            $questionId = $que_obj->questionId;
                            $AnswerChoice = $que_obj->AnswerChoice;
                            $ans = 0;
                            $que = [];
                            $choices = [];
                            if (count($AnswerChoice)) {
                                foreach ($AnswerChoice as $a => $choice) {
                                    $choices[$a]['text'] = $choice->_;
                                    $choices[$a]['answerId'] = $choice->answerId;
                                    $choices[$a]['c'] = $choice->correctAnswer == 1 ? true : false;
                                }
                            }
                            $que['questionId'] = $questionId;
                            $que['question'] = $question;
                            $que['choices'] = $choices;
                            $equifax_questions[] = $que;
                        }
                        $record->equifax_response = serialize($resp);
                        $record->equifax_transaction_id = $TransactionKey;
                        $record->save();

                        $equifax_resp['response'] = true;
                        $equifax_resp['check_status'] = true;
                        $equifax_resp['question'] = $equifax_questions;
                        $equifax_resp['equifax_transaction_id'] = $TransactionKey;
                        $equifax_resp['interactive_query_id'] = $interactiveQueryId;
                        return $equifax_resp;
                    }
                }
            }
        }
        if (count($form)) {
            $ret['id'] = $record->id;
            $ret['message'] = 'Record has been updated.';
            $ret['error_message'] = '';
            $ret['response'] = true;
        }
        return $ret;
    }
}
