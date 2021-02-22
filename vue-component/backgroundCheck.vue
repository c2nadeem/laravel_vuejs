<template>
  <div class="online_background_check_service">
	<div class="container clearfix">
      <!--container start -->
      <div class="pull-left logo">
        <a href="#"><img src="/images/logo.svg" alt="" /></a>
      </div>
      <div class="pull-right help">
        <a href="#"
          ><font-awesome-icon icon="comment" /> HELP</a
        >
		&nbsp;
        <a v-if="is_logged_in" @click="logout"
          > Logout</a
        >
      </div>
      <div class="clearfix"></div>
      <div class="progreebar-block-top"></div>
      <div class="progreebar-block"  v-if="is_policeman == false">
        <div class="progress-container">
          <div
            id="progress"
            class="progress"
            :style="{ width: progress + '%' }"
          ></div>
          <span class="pro-text">{{ progress }}%</span>
        </div>
      </div>
      <!--formarea -->
      <!--formarea start -->
      <!--mainform start -->
	  <div class="mainform" style="padding: 0;">
		  <!-- Next / Prev -->
			<div class="next-prev-box" v-if="is_policeman == false && is_completed == false" >
				<div class="next-btn" v-if="this.current_step > 2 && this.current_step < 13 && this.last_completed_step < 13">
					<button @click="goToPrevStep"
					>
					<font-awesome-icon icon="arrow-circle-left" />
					</button>
				</div>
				<div class="prev-btn"  v-if="this.current_step < this.last_completed_step ">
					<button @click="goToNextStep"
					>
					<font-awesome-icon icon="arrow-circle-right" />
					</button>
				</div>
			</div>
			<!-- Next / Prev -->
			<div class="mainform alert_wrapper" v-if="error== true || success == true">
				<div class="alert alert-primary" role="alert" v-if="error" v-html="error_message">
					
				</div>
				<div class="alert alert-success" role="alert" v-if="success">
					{{success_message}}
				</div>
			</div>
	  </div>
	  
	  	<div class="mainform" v-if="if_unfinished_record_exist && is_policeman == false && bg_check_type == ''" >
		  	<div v-if="has_unfinished_record" class="alert alert-info" role="alert">
				You have an unfinished record. Do you want to resume? 
				<button @click="resume_unfinished_record">{{"Resume Now"}}</button>
			</div>
		</div>
	  	
      

        <!-- step-1 -->
        <div
          class="online_bg_step form-area"
          :class="{ active_step: current_step == 1 }"
        >
			<div class="mainform">
				<div class="login_form_wrapper" v-if="auth_form_type == 'login'">
					<b-form @submit.prevent="doLogin" ref="login_form">
        			<!-- form start-->

						<h1>
						Login 
						</h1>
						
						<div class="ant-form-item-control">
							<b-form-input
								id="email_address"
								v-model="login.email"
								class="form-control txtbox1"
								type="email"
								:required="current_step == 1"
								placeholder="Email Address"
							></b-form-input>
						</div>
						<div class="ant-form-item-control">
							<b-form-input
								v-model="login.password"
								class="form-control txtbox1"
								type="password"
								placeholder="Password"
								:required="current_step == 1"
							></b-form-input>
						</div>
						<button class="start-btn step-btn-1">LOGIN</button>
					</b-form>
				</div>
				<div class="register_form_wrapper" v-if="auth_form_type == 'register'">
					<b-form @submit.prevent="doRegister" ref="login_form">
						<h1>
						Create a <strong>GLOBE<i>IA</i></strong> Account
						</h1>
						<div class="ant-form-item-control">
							<b-form-input
								id="first_name"
								v-model="register.first_name"
								class="form-control txtbox1"
								type="text"
								@keypress="verify_text_mask($event)"
								placeholder="First Name"
								required
							></b-form-input>
						</div>
						<div class="ant-form-item-control">
							<b-form-input
								id="last_name"
								v-model="register.last_name"
								class="form-control txtbox1"
								type="text"
								@keypress="verify_text_mask($event)"
								placeholder="Last Name"
								required
							></b-form-input>
						</div>
						<div class="ant-form-item-control">
							<b-form-input
								id="email_address"
								v-model="register.email"
								class="form-control txtbox1"
								type="email"
								placeholder="Email Address"
								required
							></b-form-input>
						</div>
						<div class="ant-form-item-control">
							<b-form-input
								v-model="register.password"
								class="form-control txtbox1"
								type="password"
								placeholder="Password"
								required
							></b-form-input>
						</div>
						<div class="ant-form-item-control">
							<b-form-input
								v-model="register.cpassword"
								class="form-control txtbox1"
								type="password"
								placeholder="Confirm Password"
								required
							></b-form-input>
						</div>
						<button class="start-btn step-btn-1">REGISTER</button>
					</b-form>
				</div>
				<div class="forget_password_form_wrapper" v-if="auth_form_type == 'forget_pass'">
					<h1>
						Reset you password
					</h1>
					<b-form @submit.prevent="doBgCheckPasswordReset" ref="login_form">
						<div class="ant-form-item-control">
							<b-form-input
								id="email_address"
								v-model="password_reset_email"
								class="form-control txtbox1"
								type="email"
								placeholder="Email Address"
								:required="current_step == 1"
							></b-form-input>
						</div>
						<button class="start-btn step-btn-1">SUBMIT</button>
					</b-form>
				</div>
				<template v-if="is_policeman != true">
					<div class="signin-link" v-if="(auth_form_type == 'register' && auth_form_type != 'login') || auth_form_type == 'forget_pass'">
						<a @click="switchAuthForm('login')"> <span>Already have an account?</span></a>
					</div>
					<div class="signin-link" v-if="(auth_form_type == 'login' && auth_form_type != 'register') || auth_form_type == 'forget_pass'">
						<a @click="switchAuthForm('register')"> <span>Create new account?</span></a>
					</div>
					<div class="forgot-pswrd" v-if="auth_form_type != 'forget_pass'">
						<a @click="switchAuthForm('forget_pass')"> <span>Forgot your password?</span></a>
					</div>
				</template>
			</div>
        </div>
    
	<b-form @submit="handleSubmit" ref="online_background_check_form" v-if="form.search_ofrcmp_national_repo != undefined">
        <!-- step-1 -->
        <!-- step-2 -->
		<template v-if="is_policeman == false">
			<div
				class="step-2 online_bg_step"
				:class="{ active_step: current_step == 2 }"
			>
			
			
			<div class="form-area">
				<div class="mainform"  v-if="bg_check_type == ''">
					<b-form-group>
						<div class="row">
							<div class="col-md-12">
								<h4 class="text-left bg_type_wrapper">Please select the type of check you need. <br/>Don’t know what to select? <a target="_blank" href="https://globeia.ca/contact/">We can help you</a></h4>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class=" police_criminal_wrapper">
									<b-form-radio @change="update_bg_check_fee" name="bg_check_type" value="police_criminal_records"><strong>Police Criminal Record Check </strong></b-form-radio>
								</div>
							</div>
							<div class="col-md-12">
								<div class=" police_criminal_wrapper">
									<b-form-radio @change="update_bg_check_fee" name="bg_check_type" value="police_criminal_and_judicial_records"><strong>Police Criminal Record and Judicial Matters Check</strong></b-form-radio>
								</div>
							</div>
						</div>
					</b-form-group>
				</div>
				<div class="secondform" v-if="bg_check_type == 'police_criminal_records'">
					<h1>
					You are about to start a background check.<br />
					Please confirm the following
					</h1>
					
					<div class="form-group form-check">
						<b-form-checkbox 
						v-model="form.search_ofrcmp_national_repo.selected_value"
						:class="'required'"
						:required="current_step == 2"
						name="search_ofrcmp_national_repo"
						>I constent to the search of the RCMP National Repository of
						Criminal Records and that i will provide my name, date of bith
						and declared criminal record history myself</b-form-checkbox
						>
					</div>

					<div class="form-group form-check">
						<b-form-checkbox  
							v-model="form.understand_varification_process.selected_value"
							:required="current_step == 2"
							name="understand_varification_process"
						:class="'required'"
						>I understand this verification process is not being confirmed by fingerprint and that fingerprint is the only true means by which to confirm if a criminal record exists
						</b-form-checkbox>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox
						v-model="form.acknowledge_that_globeia_is_thirdparty.selected_value"
						:required="current_step == 2"
						name="acknowledge_that_globeia_is_thirdparty"
						:class="'required'"
						>
						I acknowledge that Globeia is a third party check provider
						</b-form-checkbox>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox
							v-model="form.search_of_police_info.selected_value"
							:required="current_step == 2"
							:class="'required'"
							name="search_of_police_info"
						>
						I consent to the search of police information systems, as part of a Police Information Check, which will consist of a search in CPIC Investigative Data Bank
						</b-form-checkbox>
					</div>
					<div class="form-group form-check">
						<label class="form-check-label"
						>Have more questions? Check out our FAQ, or get started below</label
						>
					</div>
					<div class="centerbtns">
						<b-form-input 
							placeholder="Enter full name to Continue" 
							v-model="form.customer_initial.selected_value"
							:required="current_step == 2"
							
							@keypress="verify_text_mask($event)"
							class="required continue-btn uppercase_text"
							
						></b-form-input>
						<button class="backcheck-btn step-btn-2" @click="goForNextStep">
						Start my background check
						</button>
						<button class="backcheck-btn step-btn-2" @click="reset_bg_check_fee">
						Back
						</button>
					</div>
				</div>
				<div class="secondform" v-if="bg_check_type == 'police_criminal_and_judicial_records'">
					<h1>
						By checking the box you agree to the Judicial Matters Check and you understand the following searches will be performed.
					</h1>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.criminal_offence_individual_convicted.selected_value"
							:class="'required'"
							:required="current_step == 2"	
							name="criminal_offence_individual_convicted"
						>
							Every criminal offence of which the individual has been convicted for which a pardon/record suspensionhas not been issued or granted.
							It will not include summary convictions if the request is made more than five years after the date of the summary conviction. 
						</b-form-checkbox
						>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.finding_guilt_under_ycja.selected_value"
							:class="'required'"
							:required="current_step == 2"	
							name="finding_guilt_under_ycja"
						>
							Every finding of guilt under the YCJA in respect of the individual during the applicable period of accessunder that Act.
							Note: Only if requested in accordance with YCJA 119(1)(0). 
						</b-form-checkbox
						>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.criminal_offence_individual_found_guilty.selected_value"
							:class="'required'"
							:required="current_step == 2"	
							name="criminal_offence_individual_found_guilty"
						>
							Every criminal offence of which the individual has been found guilty and received a conditional descharge on conditions set out in a problem
							order it will not include the request if it is made of more than three years after the date of the conditional discharge.
						</b-form-checkbox
						>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.criminal_offence_outstanding_charge.selected_value"
							:class="'required'"
							:required="current_step == 2"	
							name="criminal_offence_outstanding_charge"
						>
							Every criminal offence which there is an outstanding charge or warrant to arrest in respect of the individual.
						</b-form-checkbox
						>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.court_order_against_individual.selected_value"
							:class="'required'"
							:required="current_step == 2"
							name="court_order_against_individual"
						>
							Every court order made against the individual. It wil <strong><u>not</u></strong> include court orders made under the Mental Health Act
							or under Part XX. 1 of the Criminal Code of Canada. Will <strong><u>not</u></strong> include orders made in relation ot a charge that has been withdrawn.
							Will <strong><u>not</u></strong> include restraining orders made against the individual under the Family Law Act, the Children's Law Reform Act or the Child 
							and Family Services Act.
						</b-form-checkbox
						>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox 
							v-model="form.condition_which_pardon_suspension.selected_value"
							:class="'required'"
							:required="current_step == 2"
							name="condition_which_pardon_suspension"
						>
							Any conviction for which a pardon/record suspension has been granted. It will not be included unless disclosure is authorized under the Criminal Records Act.
						</b-form-checkbox>
					</div>
					<div class="centerbtns">
						<b-form-input 
							placeholder="Enter full name to Continue" 
							@keypress="verify_text_mask($event)"
							v-model="form.customer_initial.selected_value"
							:required="current_step == 2"	
							class="required continue-btn uppercase_text"
							
						></b-form-input>
						<button class="backcheck-btn step-btn-2" @click="goForNextStep">
						Start my background check
						</button>
						<button class="backcheck-btn step-btn-2" @click="reset_bg_check_fee">
						Back
						</button>
					</div>
				</div>
			</div>
			</div>
			<!-- step-2 -->
			<!-- step-3 -->
			<div
			class="step-3 online_bg_step mainform"
			:class="{ active_step: current_step == 3 }"
			>
				<div class="form-area">
					<h1>Personal Information</h1>
					<div class="ant-form-item-control">
						<input
							v-model="form.legal_first_name.selected_value"
							type="text"
							@keypress="verify_text_mask($event)"
							class="form-control txtbox1 required uppercase_text"
							placeholder="Legal First Name"
							:required="current_step == 3"

						/>
					</div>
					<div class="ant-form-item-control">
						<input
							v-model="form.middle_name.selected_value"
							type="text"
							@keypress="verify_text_mask($event)"
							class="form-control txtbox1 uppercase_text"
							placeholder="Middle Name (if applicable)"
							
						/>
					</div>
					<div class="ant-form-item-control">
						<input
							v-model="form.legal_lasst_name.selected_value"
							type="text"
							@keypress="verify_text_mask($event)"
							class="form-control txtbox1 required uppercase_text"
							placeholder="Legal Last Name"
							:required="current_step == 3"
						/>
					</div>
					<div class="ant-form-item-control">
						<input
							v-model="form.former_name.selected_value"
							type="text"
							@keypress="verify_text_mask($event)"
							class="form-control txtbox1 uppercase_text"
							placeholder="Former Name (if applicable)"
							
						/>
					</div>
					<div class="ant-form-item-control">
						
						<b-form-input
								outline
								v-model="form.date_of_birth_name.selected_value"
								placeholder="Date of Birth (YYYY-MM-DD)"
								class="form-control txtbox1 required personal__date_of_birth"
								v-mask="'####-##-##'"
								:required="current_step == 3"
								:state="dbo_validation"
								@blur="validate_dob_date('personal__dob')"
						></b-form-input>

						
					</div>
					<div class="ant-form-item-control">
						<select class="mdb-select md-form form-control txtbox1 required" v-model="form.select_your_sex.selected_value">
							<option value="">Select your Gender</option>
							<option value="Male">Male</option>
							<option value="Female">Female</option>
						</select>
					</div>
					<div class="ant-form-item-control">
						<input
							v-model="form.phone_number.selected_value"
							type="text"
							v-mask="'###############'"
							class="form-control md-form txtbox1 required"
							placeholder="Phone Number"
							:required="current_step == 3"
						/>
					</div>
					<div class="space-div"></div>
					<button class="start-btn step-btn-3" :disabled="dbo_validation == false">NEXT</button>
					<div class="form-group form-check">
						<b-form-checkbox
							v-model="form.accept_privacy_policy.selected_value"
							:required="current_step == 3"
							name="accept_policy"
						>
							I accept the Privacy Policy and Terms of Use and consent to Globeia obtaining a criminal record on you in connection with your request.

	•   
						</b-form-checkbox>
					</div>
					<div class="form-group form-check">
						<b-form-checkbox
							v-model="form.forever_discharge_all_members.selected_value"
							:required="current_step == 3"
							name="forever_discharge_all"
						>
							I hereby and forever discharge all members and employees of the porcessing Police Service and the Royal Canadian Mounted police from any and all actions, claims and damages, loss or injury howsoever arising which may herafter be sustained by myself as a result of the disclosure of information by the Police Station to Globeia, Toronto, Canada.
						</b-form-checkbox>
					</div>
				</div>
			</div>
			<!-- step-3 -->
			<!-- step-4 -->
			<div
			class="step-4 online_bg_step mainform"
			:class="{ active_step: current_step == 4 }"
			>
				<div class="form-area" >
					<h1>Please specify your birthplace in order for authorities to search their records</h1>
					<div class="mainform">
						<div class="ant-form-item-control">  
							<b-form-group id="card_country">
								<v-select 
									:options="countries" 
									:reduce="country => country.value" 
									label="country"
									v-model="form.birth_country.selected_value"
									class="form-control txtbox1 uppercase_text"  
								/>
								
							</b-form-group>
						</div>
						<div class="ant-form-item-control">
							<b-form-group id="card_province">
								<input
									type="text"
									class="form-control txtbox1 required uppercase_text"  
									v-model="form.provice_state_of_birth.selected_value"
									placeholder="Province/State" 
									@keypress="verify_text_mask($event)"
									:required="current_step == 4"
									name="provice_state_of_birth"
								>
							</b-form-group>
						</div>
						<div class="ant-form-item-control">
							<input
								type="text"
								class="form-control txtbox1 required uppercase_text"  
								v-model="form.city_municipalit_of_birth.selected_value"
								placeholder="City" 
								@keypress="verify_text_mask($event)"
								:required="current_step == 4"
								name="city_municipalit_of_birth"

							>
						</div>
						
						<div class="space-div"></div>
						<button class="start-btn step-btn-4">NEXT</button>
					</div>
				</div>
			</div>
			<!-- step-4 -->
			<!-- step-5 -->
			<div class="online_bg_step step-5" :class="{ active_step: current_step == 5 }">
				<div class="form-area" >
					<h1>Please provide your current address</h1>
					<div class="mainform">
						
						<div class="ant-form-item-control">
							<vue-google-autocomplete
								ref="address"
								id="map"
								classname="form-control form-control txtbox1 uppercase_text get_google_address"
								placeholder="Address Lookup"
								v-on:placechanged="getAddressData"
								
								
							>
							</vue-google-autocomplete>
							
						</div>
						<div class="ant-form-item-control">
							<input type="text" 
								class="form-control txtbox1 uppercase_text required"  
								placeholder="Street Address" 
								v-model="form.current_street_address.selected_value"
								:required="current_step == 5"
							> 
						</div>
						<div class="ant-form-item-control">
							<input 
								type="text" 
								class="form-control uppercase_text txtbox1"  
								v-model="form.unit_number.selected_value" 
								placeholder="Unit Number / Name (eg: '305')" 
							> 
						</div>
						<div class="ant-form-item-control">
							<b-form-group id="card_country">
								<v-select 
									:options="countries" 
									:reduce="country => country.value" 
									label="country" 
									v-model="form.current_country.selected_value"
									class="form-control txtbox1 uppercase_text"  
								/>
							</b-form-group>
						</div>
						<div class="ant-form-item-control">
							<b-form-group id="card_province">
								<input
									type="text"
									class="form-control txtbox1 uppercase_text required"  
									v-model="form.current_provice.selected_value"
									placeholder="Province/State" 
									@keypress="verify_text_mask($event)"
									:required="current_step == 5"
									name="current_provice"
								>
							</b-form-group>
						</div>
						<div class="ant-form-item-control">
							<input
								type="text"
								class="form-control txtbox1 uppercase_text required"  
								v-model="form.current_city.selected_value"
								placeholder="City" 
								:required="current_step == 5"
								@keypress="verify_text_mask($event)"
								name="city"
							>
						</div>
						<div class="ant-form-item-control">
							<input 
								type="text" 
								class="form-control txtbox1 uppercase_text required"  
								v-model="form.current_postal.selected_value" 
								placeholder="Postal / Zip Code" 
								:required="current_step == 5"
								@keypress="verify_zip_mask($event)"
							>
							</div>
							
							<div class="space-div"></div>
							<button class="start-btn step-btn-5">NEXT</button>
						</div>
					</div>
				</div>
				
				<!-- step-5 -->
				<!-- step-6 -->
				<div class="online_bg_step step-6" 
					:class="{ active_step: current_step == 6 }"
				>
					<div class="form-area" >
						<h1>Please answer the following questions</h1>
						<div class="mainform-2">
							<template v-if="equifax_questions.length > 0">
								<div class="row" v-for="(question, ind) in equifax_questions" :key="ind">
									<div class="col-md-12">
										<p style="text-align: left;"><strong>Q. {{question.question}}</strong></p>
										<ul>
											<li v-for="(choice, key) in question.choices" :key="key" style="list-style: none;">
												<label>
													<input type="radio" :name="'que_'+question.questionId" :value="question.questionId+'_'+choice.answerId" v-model="equifax_temp_answers[ind]" />
													{{choice.text}}
												</label>
											</li>
										</ul>
									</div>
								</div>
							</template>
							<div class="space-div"></div>
							<button class="start-btn step-btn-6">NEXT</button>
						</div>
					</div>
				</div>
				<!-- step-6 -->
				<!-- step-7 a -->
				<div class="online_bg_step step-7-a" 
					:class="{ active_step: current_step == 7 }"
				>
					<div class="form-area">
						<h1><a href="#"><img src="/images/icon-1.png" alt="" /></a>You did not pass validation, we need additional steps from you</h1>
						<h3>Take a picture of your ID #1</h3>
						<P>Make sure your document is not expired, is in full colour and not truncated</P>
						<div class="mainform-2">
							<div class="row">
								<div class="col-md-6 col-sm-12">
									<div class="upload-front" >
										<img style="max-width: 100%;" v-if="form.front_picture_of_id_1.selected_value != ''" :src="form.front_picture_of_id_1.selected_value" />
									</div>
										<br>
										<v-flex xs12>
											<b-form-file
												v-model="front_picture_of_id_1"
												placeholder="Upload Front"
												:file-name-formatter="formatFileNames"
												drop-placeholder="Drop file here..."
												accept="image/*"
												@change="upload_ID_front"
												browse-text="Upload Front of ID #1"
												:required="current_step == 7 && form.front_picture_of_id_1.selected_value == ''"
												ref="file_id_1"
											></b-form-file>
										</v-flex>
									
								</div>
								<div class="col-md-6 col-sm-12">
									<div class="upload-front" >
										<img style="max-width: 100%;"  v-if="form.back_picture_of_id_1.selected_value != ''" :src="form.back_picture_of_id_1.selected_value" />
									</div>
										<br>
										<v-flex xs12>
											<b-form-file
												v-model="back_picture_of_id_1"
												placeholder="Upload Back"
												:file-name-formatter="formatFileNames"
												drop-placeholder="Drop file here..."
												accept="image/*"
												@change="upload_ID_back"
												browse-text="Upload Reverse of ID #1"
												ref="file_id_1"
												:required="current_step == 7 && form.back_picture_of_id_1.selected_value == ''"
											></b-form-file>
										</v-flex>
									
								</div>
								<div class="col-md-12">
									<h3 class="h3head">Please clarify the Country and the Type of Identification of ID #1</h3>
								</div>
							</div>
							<div class="mainform">
								<div class="space-div2"></div>
								<div class="ant-form-item-control">
									<label class="label">Select the issuing Country / Region on ID #1</label>
									
									<template v-if="identify_id_countries.length > 0">
										<v-select 
											:value="form.issuing_country_reqion.selected_value" 
											@input="selectIdentificationCountry" 
											:options="identify_id_countries" 
											v-model="country"
											@keypress="verify_text_mask($event)"
											class="form-control txtbox1"
											></v-select>
									</template>
					
								</div>
								<div class="ant-form-item-control">
									<label class="label">Select ID type</label>
									<div style="line-height: 1.5em;">Use a valid government-issued photo ID.</div>
									<div style="line-height: 1.5em;">A residence permit is also a valid identity card.</div>
										<select class="mdb-select md-form form-control txtbox1 txtbox2 required" 
										v-model="form.select_id_type.selected_value"
										:required="current_step == 7"
									>	

										<option value=""> Select </option>
										<option v-for="(obj, ind) in country.values" :key="ind">{{obj.value}}</option>
									</select>
								</div>
								
								<div class="space-div"></div>
								<button class="start-btn step-btn-7">SUBMIT ID</button>
								<div class="space-div"></div>
								<div class="links_wrapper">
									<ul>
										<li>
											<a href="#" target="_blank">Why didn’t I pass the verification? </a>
										</li>
										<li>
											<a href="#" target="_blank">Why are you asking for copy my IDs? How do I upload my ID?</a>
										</li>
									</ul>
    							</div>
							</div> 
						</div>
					</div>
				</div>
				<!-- step-7 a -->
				<!-- step-7 b -->
				<div class="online_bg_step step-7-b" :class="{ active_step: current_step == 8 }">
					<div class="form-area">
						<h1><a href="#"><img src="/images/icon-1.png" alt="" /></a>You did not pass validation, we need additional steps from you: Second step for verification.</h1>
						<h2>Record a short video saying your full name</h2>
						<div class="mainform-2">    
							<div class="row">
								<div class="col-md-10 offset-md-1">
									<div class="start-record" v-if="form.record_short_video.selected_value != ''">
										<video controls>
											<source :src="form.record_short_video.selected_value" type="video/mp4">
											Your browser does not support HTML video.
										</video>
									</div>
									
										<v-flex xs12>
											<b-form-file
												v-model="record_short_video"
												placeholder="Upload Video"
												:file-name-formatter="formatFileNames"
												drop-placeholder="Drop file here..."
												browse-text="Upload Video"
												ref="record_short_video"
												accept="video/*"
												:required="current_step == 8"
											></b-form-file>
										</v-flex>
									
								</div>
							</div>
							<button class="start-btn submit-btn step-btn-7">SUBMIT</button>
							<div class="links_wrapper text-center">
									<ul>
										<li>
											<a href="#" target="_blank">Why are you asking to upload the video? How do I upload my video?</a>
										</li>
									</ul>
    							</div>
						</div>
					</div>
				</div>
				<!-- step-7 b -->
				<!-- step-8  -->
				<div class="online_bg_step step-8" :class="{ active_step: current_step == 9 }">
					<div class="form-area">
						<h1>You have successfully completed the verification request.<br />
								Please continue to the next page.</h1>
						<div class="mainform-2">
							<img src="/images/thumb-icon.png" class="thumb-style" alt="" />  
							<div class="space-div"></div>
							<button class="start-btn nextbtn step-btn-8-b">NEXT</button>
						</div>
					</div>
				</div>
				<!-- step-8 -->
				<!-- step-8-b -->
				<div class="online_bg_step step-8-b" :class="{ active_step: current_step == 10 }">
					<div class="form-area">
						<h1>Have you been convicted of any crimes?</h1>
						<p class="para-txt">List all convictions you’ve had in your lifetime. Since we know it’s difficult to remember convictions that happened years ago, we’ll let you know by email if something doesn’t seem right.<br /></p>
						<div class="mainform-2">
							<div class="row crm-box">
							<div class="col-6">
								<a @click="not_convicted_crime" class="nobox step-btn-9">
									<h3>NO</h3>
									<p>I have not been convicted of a crime</p>
								</a>
							</div>
							<div class="col-6">
								<a @click="convicted_crime" class="nobox step-btn-9">
									<h3>YES</h3>
									<p>I have been convicted of a crime</p>
								</a>           
							</div>
							</div>          
						</div>
					</div>
				</div>
				<!-- step-8-b -->
				<!-- step-9 -->
				<div class="online_bg_step step-9" :class="{ active_step: current_step == 11 }">
					<div class="form-area">
						<h1>Now, does the following apply to you?</h1>
						<div class="mainform mainform-3">
							<ul>
								<li  class="textstyle1">Do not declare the following:</li>
								<li  class="textstyle2">A conviction for which you have received a Record Suspension (formerly pardon) in accordance with the Criminal Records Act</li>
								<li  class="textstyle2">A conviction where you were a 'young person' under the Youth Criminal Justice Act</li>
								<li  class="textstyle2">An Absolute or Conditional Discharge, pursuant to section 730 of the Criminal Code</li>
								<li  class="textstyle2">An offence for which you were not convicted</li>
								<li  class="textstyle2">Any provincial or municipal offence</li>
								<li  class="textstyle2">Any charges dealt with outside of Canada</li>
								<li  class="textstyle2">Note that a Certified Criminal Record can only be issued based on the submission of fingerprints to the RCMP National Repository of Criminal Records </li>    
							</ul>
							<div class="conviction_form_wrapper">
								<div class="conviction_form_item" v-for="(convic_item, ind) in convictions" :key="ind">
									<h4>Conviction #{{(ind+1)}}</h4>
									<div class="ant-form-item-control">
										<input
											type="text"
											class="form-control txtbox1 required uppercase_text"  
											v-model="convic_item.offence"
											placeholder="Conviction Offence" 
											:required="current_step == 11"
										>
									</div>
									<div class="ant-form-item-control">
										<b-form-input
											outline
											v-model="convic_item.date"
											placeholder="DATE (YYYY-MM-DD)"
											class="form-control txtbox1 required date_of_sentense"
											v-mask="'####-##-##'"
											:required="current_step == 11"
											:state="dos_validation[ind]"
											@blur="validate_convic_date"
											
										></b-form-input> 
										
									</div>
									<div class="ant-form-item-control">
										<input
											type="text"
											class="form-control txtbox1 required  uppercase_text"
											v-model="convic_item.location"
											placeholder="Court Location- Court Location, City, State, Country"
											:required="current_step == 11"
										>
									</div>
								</div>
								<div class="add_more_convictions">
									<a v-if="convictions.length > 1" @click="remove_convictions"><span>-</span>Remove</a>
									<a @click="add_more_convictions"><span>+</span>add more</a>
								</div>
							</div>
							<div class="space-div"></div>
							<button class="start-btn nextbtn step-btn-10-a" :disabled="dos_validation_btn == false">NEXT</button>
						</div>
					</div>
				</div>
				<!-- step-9 -->
			</template>
			<!-- step-10-a -->
			<div class="online_bg_step step-10-a" :class="{ active_step: current_step == 12 }">
				<div class="form-area" >
					<h1>Confirm your details</h1>
					<P>Please review your details below carefully, Once you confirm, you won't be able to edit<br />
						these details again.</P>
					<div class="mainform details-box confirm_details">
					<!-- first step info 1 -->
						<div class="row">
							<div class="infobox-1 clearfix">
								<div class="tophead clearfix">
									<div class="pull-left">
										<h2>Personal Information</h2>
									</div>
									<div class="pull-right">
										<a @click="goToStep(3)" v-if="is_policeman == false" class="edit-icon edit_section"><font-awesome-icon icon="edit" /> Edit Section</a>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>First Name</p>
										<div class="label">{{form.legal_first_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Middle Name</p>
										<div class="label">{{form.middle_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Last Name</p>
										<div class="label">{{form.legal_lasst_name.selected_value}}</div>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Former Name</p>
										<div class="label">{{form.former_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Date of Birth (YYYY-MM-DD)</p>
										<div class="label">{{form.date_of_birth_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Mobile Number</p>
										<div class="label">{{form.phone_number.selected_value}}</div>
									</div>
									
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Sex</p>
										<div class="label">{{form.select_your_sex.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Birthplace</p>
										<div class="label">
											<span v-if="form.city_municipalit_of_birth != undefined">
												{{form.city_municipalit_of_birth.selected_value}}, 
											</span>
											<span v-if="form.provice_state_of_birth != undefined">
												{{form.provice_state_of_birth.selected_value}}, 
											</span>
											<span v-if="form.birth_country != undefined">
												{{form.birth_country.selected_value}}
											</span>

										</div>
									</div>
									
									
								</div>
							</div>
						</div>
						<!-- first step info 1 end -->
						<!-- second step info -->
						<div class="row">
							<div class="infobox-1 clearfix">
								<div class="tophead clearfix">
									<div class="pull-left">
										<h2>Address</h2>
									</div>
									<div class="pull-right">
										<a @click="goToStep(5)" class="edit-icon edit_section"><font-awesome-icon icon="edit" /> Edit Section</a>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Street Address</p>
										<div class="label">{{form.current_street_address.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>City</p>
										<div class="label">{{form.current_city.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Country</p>
										<div class="label">{{form.current_country.selected_value}}</div>
									</div>
								</div>
							<div class="row innersection">
								<div class="col-md-4 col-sm-12">
									<p>Province</p>
									<div class="label">{{form.current_provice.selected_value}}</div>
								</div>
								<div class="col-md-4 col-sm-12">
									<p>Postal Code</p>
									<div class="label">{{form.current_postal.selected_value}}</div>
								</div>
								<div class="col-md-4 col-sm-12">
									<p>Unit Number / Name</p>
									<div class="label">{{form.unit_number.selected_value}}</div>
								</div>
							</div>
						</div>
					</div>
					<!-- second step info 2 end -->
					<!-- third step info -->
					<div class="row" v-if="convictions[0].date != undefined && convictions[0].date != ''">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>History</h2>
								</div>
								<div class="pull-right">
									<a @click="goToStep(9)" class="edit-icon edit_section"><font-awesome-icon icon="edit" /> Edit Section</a>
								</div>
							</div>
							<div class="row innersection">
								<div class="col-md-12 col-sm-12">
									<p>Conviction(s)

										<a class="pull-right edit-icon edit_section" @click="goToStep(11)"><font-awesome-icon icon="edit" /> Edit Section</a>
									</p>
								</div>
							</div>
							<div class="row innersection conviction_table">
								<table class="table">
									<thead>
										<tr>
											<th>#</th>
											<th>Date of Sentence</th>
											<th>Court Location</th>
											<th>Offence</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(convic, ind) in convictions" :key="ind">
											<td>{{(ind+1)}}</td>
											<td>{{convic.date}}</td>
											<td>{{convic.location}}</td>
											<td>{{convic.offence}}</td>
										</tr>
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					<!-- thrid step info  end -->   
					<div class="space-div"></div>
					<button v-if="is_policeman == false" class="start-btn submit-btn step-btn-10-b">CONFIRM</button>
				</div>
			</div>
			
		</div>
		<!-- step-10-a -->
		<template v-if="is_policeman == false">
			<div class="online_bg_step step-13 payment_form_wrapper" :class="{ active_step: current_step == 13 }">
				<div class="form-area">
					<h4>Payment Information</h4>
					
					<div class="offset-md-3 col-md-6 col-sm-12 fee_wrapper">
						<div class="row">
							<div class="col-md-8 ">
								<h6 v-if="form.bg_check_type.selected_value == 'police_criminal_and_judicial_records'">
									Judicial Matters Check Fee
								</h6>
								<h6 v-else>
									Police Criminal Record Check Fee
								</h6>
							</div>	
							<div class="col-md-4">
								${{fee}}
							</div>	
						</div>	
						<div class="row" v-if="want_bg_check_copy">
							<div class="col-md-8">
								<h6>{{shipping.text}}</h6>
							</div>	
							<div class="col-md-4">
								${{shipping.shipping_amount}}
							</div>	
						</div>
						<div class="row">
							<div class="col-md-12">
								<hr />
							</div>	
						</div>
						<div class="row">
							<div class="col-md-8">
								<h6>Subtotal</h6>
							</div>	
							<div class="col-md-4">
								${{subtotal}}
							</div>	
						</div>
						<div class="row">
							<div class="col-md-8">
								<h6>Tax ({{tax}}% HST)</h6>
							</div>	
							<div class="col-md-4">
								${{tax_amount}}
							</div>	
						</div>
						<hr />
						<div class="row">
							<div class="col-md-8">
								<h6><strong>Total</strong></h6>
							</div>	
							<div class="col-md-4">
								<strong>${{total_fee}}</strong>
							</div>	
						</div>
						<hr />	
					</div>
					<br><br>
					<div class="offset-md-3 col-md-6 col-sm-12" style="background: #d9d9d9; padding: 20px;">
						<div class="row">
							<div class="col-md-12 col-sm-12 text-left align-left delivery_info_wrapper">
								<h4 style="text-align: left; padding-top: 0;">Delivery Information</h4>
								<p>You have submitted your application for online criminal record check. You will receive the results by email. If you like to receive the results by mail, please select the mailing option and provide us the address.</p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 col-sm-12">
								<b-form-checkbox v-model="preselected" :disabled="true">
									I will receive my results by email.
								</b-form-checkbox>
								<b-form-checkbox v-model="want_bg_check_copy" @change="update_total($event)">
									I also want a hard copy of background check.
								</b-form-checkbox>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 col-sm-12"  v-if="want_bg_check_copy">
								<br>
								
								<b-form-input
									outline
									v-model="shipping_to"
									@keypress="verify_text_mask($event)"
									placeholder="RECIPIENT FULL NAME"
									class="form-control txtbox1 required"
									
								></b-form-input>
							</div>
							<div class="col-md-12 col-sm-12"  v-if="want_bg_check_copy">
								<br>
								<textarea v-model="shipping_address" placeholder="DELIVERY ADDRESS"
								 	:required="want_bg_check_copy"
								 	class="form-control txtbox1"
								 ></textarea>
								 <br>
							</div>
							<div class="col-md-12 col-sm-12" v-if="want_bg_check_copy">
								<h5>Shipping options (pick one of 3):</h5>
								<b-form-group >
									<b-form-radio v-model="shipping_method" @change="update_shipping" name="shipping_method" value="10">Canada Post non-trackable mail - $10 </b-form-radio>
									<b-form-radio v-model="shipping_method" @change="update_shipping" name="shipping_method" value="30">Expedited (1-2 Day Courier) Within Canada - $30</b-form-radio>
									<b-form-radio v-model="shipping_method" @change="update_shipping" name="shipping_method" value="We will contact you">I want to send this via international courier - (we will contact you)</b-form-radio>
								</b-form-group>
								<hr />
							</div>
						</div>
					</div>
					<br><br>
					<div class="row">
						<div class="offset-md-3  col-md-6 col-sm-12 text-left align-left">
							<h4 style="text-align: left; padding-top: 0;">Payment Information</h4>
						</div>
					</div>
					<div class="row">
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group
								id="card_name_on_card"
							>
								<b-form-input
								outline
								@keypress="verify_text_mask($event)"
								v-model="card.name_on_card"
								label="NAME ON CARD"
								:required="current_step == 13"
								placeholder="NAME ON CARD"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group
								id="card_street_address"
								>
									<b-form-input
									outline
									v-model="card.street_address"
									:required="current_step == 13"
									placeholder="STREET ADDRESS"
									:rule="[v => !!v || 'This field is requireds',]"
								></b-form-input>
							</b-form-group>
						</div>
						
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group id="card_country">
								<v-select 
									:options="countries" 
									:reduce="country => country.code" 
									label="country" 
									v-model="card.country"
									class="form-control txtbox1 uppercase_text" 
									autocomplete = "off"
								/>
							</b-form-group>
							
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group id="card_province">
								<input
									type="text"
									class="form-control txtbox1 uppercase_text"  
									v-model="card.province"
									v-mask="'AA'"
									placeholder="PROVINCE/STATE e.g. (CA)" 
									@keypress="verify_text_mask($event)"
									:required="current_step == 13"
									name="provice_state_of_birth"
									autocomplete = "off"
								>
							</b-form-group>
							
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group id="card_city">
								<b-form-input
								outline
								v-model="card.city"
								placeholder="CITY"
								:required="current_step == 13"
								@keypress="verify_text_mask($event)"
								autocomplete = "off"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group
									id="card_postal"
								>
								<b-form-input
									outline
									v-model="card.postal"
									placeholder="POSTAL"
									:required="current_step == 13"
									autocomplete = "off"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group id="card_cc_number">
								<b-form-input
									outline
									v-model="card.cc_number"
									placeholder="CREDIT CARD NUMBER"
									:required="current_step == 13"
									autocomplete = "off"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<b-form-group id="card_exp_date">
								<b-form-input
									outline
									v-model="card.exp_date"
									placeholder="EXPIRY DATE (MM/YY)"
									v-mask="'##/##'"
									:required="current_step == 13"
									autocomplete = "off"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
								<b-form-group id="card_cvd">
								<b-form-input
									outline
									v-model="card.cvd"
									v-mask="'####'"
									placeholder="CVD"
									:required="current_step == 13"
									autocomplete = "off"
								></b-form-input>
							</b-form-group>
						</div>
						<div class="offset-md-3 col-md-6 col-sm-12">
							<button class="start-btn submit-btn step-btn-10-b">Pay Now</button>
						</div>
					</div>
				</div>
			</div>
		</template>		
		

      </b-form>
      <template v-if="progress == 100 && is_policeman == false">
			<div class="mainform mainform-2">
				<h2>Thank you!</h2>
				<p>Some thank you text.</p>
			</div>
		</template>
	  <template v-if="is_policeman == true && police_step == 1">
		  	<div class="mainform mainform-2">
		  		<div class="view_more_requests_wrap" v-if="records_to_be_checked.length > 0">
					<div class="row">
						<div class="col-md-12">
							<a @click="view_more_requests_method" class="btn btn-primary">{{ 'View Outstanding Requests' }}</a>
							
						</div>
					</div>
					<transition name="fade" v-if="view_more_requests == true">
						<div class="row view_more_requests_list">
							<div class="col-md-12">
								<b-list-group>
									<b-list-group-item v-for="(record, ind) in records_to_be_checked" :key="ind" :active="record.id == form.id" @click="switchBgRecord(record.id)" class="flex-column align-items-start">
										<div class="d-flex w-100 justify-content-between">
											<h6 class="mb-1">Requested By: <strong>{{record.user.first_name}} {{record.user.last_name}}</strong></h6>
											ID: <strong>{{record.id}}</strong>
										</div>
									</b-list-group-item>
								</b-list-group>
							</div>
						</div>
					</transition>
		  		</div>
				<div class="row" v-else>
					<div class="col-md-12">
						<div class="alert alert-info" role="alert">
							No more requests to be verified.
						</div>
					</div>
				</div>
			
			<!--mainform -->
			<div class="mainform mainform-2" v-if="record_id > 0">
				<h3>ID: {{record_id}}</h3>
				<b-form @submit.prevent="completeBackgroundCheckRequest" ref="upload_certificate_form">
					<!-- first step info 1 -->
					<div class="row" v-if="form.equifax_transaction_id != 0"> 
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>Equifax Verification of Identity</h2>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<label class="label-2">Electronic Clarification performed through Equifax
										eiDVerifier service.</label>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<label class="label-2"><strong>Transection Key
										</strong>{{form.equifax_transaction_id}}</label>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<label class="label-2"><strong>Reason(s):</strong>  </label>
									<div class="reasons_wrapper">
										<table class="table">
											<thead>
												<tr>
													<th>Code</th>
													<th>Reason</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="(reason, ind) in form.equifax_reasons" :key="ind"> 
													<td>{{reason.reason_code}}</td>
													<td>{{reason.reason}}</td>
												</tr>
											</tbody>
										</table>
										
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- first step info 1 end -->
					<!-- second step info -->
					<div class="row" v-if="form.equifax_status == false">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>Provided Identification</h2>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.front_picture_of_id_1.selected_value">* ID #1 Front and back</a>
								</div>
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.back_picture_of_id_1.selected_value">* ID #2 Front and back</a>

									
								</div>
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.record_short_video.selected_value">* Identification Video</a>
								</div>
							</div>

						</div>
					</div>
					<!-- second step info 2 end -->
					<!-- third step info -->
					<div class="row">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>Infromation Required for Background Check</h2>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<a @click="show_application_submission">* Application Submission</a>
								</div>
							</div>
						</div>
						
					</div>
					<!-- thrid step info  end -->
					<div class="row">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>Background Check Result</h2>
								</div>
							</div>
							
								<div class="row innersection innersec-2">
									<div class="col-md-12">
										<label class="label-3"><strong>Background Result</strong></label>
										<select class="mdb-select md-form form-control txtbox1 conf-txtbox" required="" v-model="bg_result_type" @change="changeBGresultText">
											<option value="">-- Select --</option>
											<option value="Confirmed Declaration">Confirmed Declaration</option>
											<option value="Negative">Negative</option>
											<option value="Incomplete">Incomplete</option>
										</select>
									</div>
									<div class="col-md-12">
										<div class="cnfr-textbox background_check_result_content" v-if="confirmed_result || negative_result || incomplete_result">
											<div class="confirmed_result" v-if="confirmed_result">
												<h2>Confirmation of a Criminal Record</h2>
												<p>
													<strong>Based solely on the name(s) and date of birth provided and the criminal record information declared by the applicant,</strong> a search of the RCMP National Repository of Criminal Records 
													<strong>has resulted in a possible match to a registered criminal record. Positive identification that a criminal record does or does not exist at the RCMP National Repository of Criminal Records can only be confirmed by fingerprint comparison. As such, the criminal record information declared by the applicant does not constitute a Certified Criminal Record by the RCMP.</strong> Delays do exist between a conviction being rendered in court, and the details being accessible on the RCMP National Repository of Criminal Records. Not all offences are reported to the RCMP National Repository of Criminal Records
												</p>
											</div>
											<div class="negative_result" v-if="negative_result">
												<h2>Negative Declaration</h2>
												<p><strong>Based solely on the name(s) and date of birth provided and the criminal record information declared by the applicant,</strong> 
												a search of the RCMP National Repository of Criminal Records <strong>did not identify any records</strong> with the name(s) and date of birth of the applicant. 
												<strong>Positive identification that a criminal record does or does not exist at the RCMP National Repository of Criminal Records can only be confirmed by fingerprint comparison.</strong> 
												Delays do exist between a conviction being rendered in court, and the details being accessible on the RCMP National Repository of Criminal Records. Not all offences are reported to the RCMP National Repository of Criminal Records</p>
											</div>
											<div class="incomplete_result" v-if="incomplete_result">
												<h2>Incomplete Declaration</h2>
												<p>
													<strong>Based solely on the name(s) and date of birth provided and the criminal record information declared by the applicant,</strong> a search of the RCMP National Repository of Criminal Records <strong>could not be completed. Positive identification that a criminal record does or does not exist requires the applicant to submit fingerprints to the RCMP National Repository of Criminal Records by an authorized police service or accredited private fingerprinting company.</strong> Delays do exist between a conviction being rendered in court, and the details being accessible on the RCMP National Repository of Criminal Records. Not all offences are reported to the RCMP National Repository of Criminal Records. Reasons why you may have received an <strong>Incomplete</strong> result:
												</p>
												<ol>
													<li>You have not declared a conviction that is on your criminal record</li>
													<li>The Declaration of Criminal Record is not complete</li>
													<li>The dates are incorrect on your Declaration of Criminal Record</li>
												</ol>
											</div>
										</div>
									</div>
									<div class="col-md-12 col-12">
										<label class="label-3"><strong>Upload Certificate</strong></label>
										<div class="col-md-10">
											<v-flex xs12>
												<b-form-file
													v-model="certificate_file"
													placeholder="Upload File"
													:file-name-formatter="formatFileNames"
													drop-placeholder="Drop file here..."
													browse-text="Upload Certificate"
													:required="police_step == 2"
													ref="file_id_1"
												></b-form-file>
											</v-flex>
										</div>
									</div>
								</div>
							
						</div>
					</div>
					<div class="space-div"></div>
					<button class="start-btn submit-btn">Submit</button>
				</b-form>	
			</div>
			<!--mainform end -->
		</div>
		<!--form-area -->
	  </template>
	  <!-- form end-->
	  	<template v-if="is_policeman == true && policeman_record.legal_first_name != undefined && police_step == 2">
		  <div class="online_bg_step step-10-a active_step">
				<div class="form-area" >
					<h1>Confirm your details </h1>
					<p>Please review following details below carefully</p>
					<div class="mainform details-box confirm_details">
						<!-- first step info 1 -->
						<div class="row">
							<div class="col-md-12 text-center">
								<p><a @click="hide_application_submission"><font-awesome-icon icon="angle-double-left" /> Back</a></p> 
								<!-- Back Police -->
							</div>
						</div>
						<div class="row">
							<div class="infobox-1 clearfix">
								<div class="tophead clearfix">
									<div class="pull-left">
										<h2>Personal Information</h2>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>First Name</p>
										<div class="label">{{policeman_record.legal_first_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Middle Name</p>
										<div class="label">{{policeman_record.middle_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Last Name</p>
										<div class="label">{{policeman_record.legal_lasst_name.selected_value}}</div>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Former Name</p>
										<div class="label">{{policeman_record.former_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Date of Birth (YYYY-MM-DD)</p>
										<div class="label">{{policeman_record.date_of_birth_name.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Mobile Number</p>
										<div class="label">{{policeman_record.phone_number.selected_value}}</div>
									</div>
									
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Sex</p>
										<div class="label">{{policeman_record.select_your_sex.selected_value}}</div >
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Birthplace</p>
										<div class="label">
											<span v-if="form.city_municipalit_of_birth != undefined">
												{{form.city_municipalit_of_birth.selected_value}}, 
											</span>
											<span v-if="form.provice_state_of_birth != undefined">
												{{form.provice_state_of_birth.selected_value}}, 
											</span>
											<span v-if="form.birth_country != undefined">
												{{form.birth_country.selected_value}}
											</span>

										</div>
									</div>
									
								</div>
							</div>
						</div>
						<div class="row">
							<div class="infobox-1 clearfix">
								<div class="tophead clearfix">
									<div class="pull-left">
										<h2>Birthplace</h2>
									</div>
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Country</p>
										<div class="label">{{policeman_record.birth_country.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>State</p>
										<div class="label">{{policeman_record.provice_state_of_birth.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>City</p>
										<div class="label">{{policeman_record.city_municipalit_of_birth.selected_value}}</div>
									</div>
								</div>
							</div>
						</div>
						<!-- first step info 1 end -->
						<!-- second step info -->
						<div class="row">
							<div class="infobox-1 clearfix">
								<div class="tophead clearfix">
									<div class="pull-left">
										<h2>Address</h2>
									</div>
									
								</div>
								<div class="row innersection">
									<div class="col-md-4 col-sm-12">
										<p>Street Address</p>
										<div class="label">{{policeman_record.current_street_address.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>City</p>
										<div class="label">{{policeman_record.current_city.selected_value}}</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<p>Country</p>
										<div class="label">{{policeman_record.current_country.selected_value}}</div>
									</div>
								</div>
							<div class="row innersection">
								<div class="col-md-4 col-sm-12">
									<p>Province</p>
									<div  class="label">{{policeman_record.current_provice.selected_value}}</div>
								</div>
								<div class="col-md-4 col-sm-12">
									<p>Postal Code</p>
									<div class="label">{{policeman_record.current_postal.selected_value}}</div>
								</div>
								
							</div>
							<div class="row innersection">
								<div class="col-md-4 col-sm-12">
									<p>Unit Number / Name</p>
									<div class="label">{{policeman_record.unit_number.selected_value}}</div>
								</div>
							</div>
						</div>
					</div>
					<!-- second step info -->
					<div class="row" v-if="form.equifax_status == false">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>Provided Identification</h2>
								</div>
							</div>
							<div class="row innersection innersec-2">
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.front_picture_of_id_1.selected_value">* ID #1 Front and back</a>
								</div>
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.back_picture_of_id_1.selected_value">* ID #2 Front and back</a>
								</div>
								<div class="col-md-12">
									<a target="_blank" :href="policeman_record.record_short_video.selected_value">* Identification Video</a>
								</div>
							</div>

						</div>
					</div>
					<!-- second step info 2 end -->
					<!-- second step info 2 end -->
					<!-- third step info -->
					<div class="row">
						<div class="infobox-1 clearfix">
							<div class="tophead clearfix">
								<div class="pull-left">
									<h2>History</h2>
								</div>
								
							</div>
							<div class="row innersection">
								<div class="col-md-4 col-sm-12">
									<p>Conviction (s)</p>
								</div>
							</div>

							<div class="row innersection convictions_wrapper conviction_table" v-if="convictions[0] != undefined && convictions[0].date != ''">
								
								<div class="col-md-12">
									<table class="table">
										<thead>
											<tr>
												<th>#</th>
												<th>Date of Sentence</th>
												<th>Court Location</th>
												<th>Offense</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(convic, ind) in convictions" :key="ind">
												<td>{{(ind+1)}}</td>
												<td>{{convic.date}}</td>
												<td>{{convic.location}}</td>
												<td>{{convic.offence}}</td>
											</tr>
										</tbody>
									</table>
								</div>
									
								
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 text-center">
							<p><a @click="hide_application_submission"><font-awesome-icon icon="angle-double-left" /> Back</a></p>
							<!-- Back Police -->
						</div>
					</div>
					<!-- thrid step info  end -->   
					<div class="space-div"></div>
					
				</div>
			</div>
			</div>
	  	</template>
	  <template v-if="policeman_record_not_found != ''">
		  <div class="alert alert-error">{{policeman_record_not_found}}</div>
	  </template>
    </div>
    <!--container end -->
  </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import { required, maxLength, email } from 'vuelidate/lib/validators';
import Vue from 'vue';
import BootstrapVue from "bootstrap-vue";
import VueMask from 'v-mask'
import _ from 'lodash';
import $ from 'jquery';
import select from '../DemoPages/Vuetify/Components/data-tables/select.vue';
import identify_countries from './countries';
import identify_countries_values from './countries_with_values';
import VueGoogleAutocomplete from 'vue-google-autocomplete'

Vue.use(VueMask);
Vue.use(BootstrapVue);
import maskedInput from 'vue-masked-input';
export default {
  components: { 
	  select: select,
	  maskedInput: maskedInput,
	  VueGoogleAutocomplete,
	  
	},
  data: () => ({
    preselected: true,
	heading: "Online Background Check",
	is_completed: false,
    subheading: "",
    current_step: 1,
    last_completed_step: 1,
    valid: false,
    steps: 14,
    progress: 0,
	form: {},
	is_logged_in: false,
	auth_form_type: 'login',
	login: {
		email: '',
		password: '',
	},
	register: {
		first_name: '',
		last_name: '',
		email: '',
		password: '',
		cpassword: '',
	},
	error: false,
	error_message: '',
	success: false,
	success_message: '',
	police_step: 0,
	record_id: 0,
	has_unfinished_record: false,
	unfinished_record: [],
	unfinished_bg_record: {},
	if_unfinished_record_exist: false,
	front_picture_of_id_1: [],
	back_picture_of_id_1: [],
	record_short_video: [],
	certificate_file: [],
	ca_provinces: [],
	us_provinces: [],
	provice_state_of_birth: [],
	current_provinces: [],
	provinces: [],
	card: {
		name_on_card: '',
		street_address: '',
		country: 'Select Country',
		outline: '',
		province: '',
		city: '',
		postal: '',
		cc_number: '',
		exp_date: '',
		cvd: '',
	},
	countries: [
      {text : 'Select Country', value : ''},
      {text : 'Canada', value : 'CA'},
      {text : 'United States of America', value : 'US'},
    ],
	accept_reject_options: [
		{text : 'Accept', value : 'accept'},
      	{text : 'Reject', value : 'reject'},
	],
	
	fee: 39,
	shipping: {
		text: 'Canada Post non-trackable mail',
		shipping_amount: 10
	},
	is_policeman: false,
	policeman_record_not_found: '',
	policeman_record: {},
	total_fee: 0,
	shipping_method: 10,
	tax: 13,
	tax_amount: 0,
	subtotal: 0,	
	reasons: [],
	equifax_questions: [],
	equifax_temp_answers: [],
	equifax_answers: [],
	equifax_transaction_id: '',
	interactive_query_id: '',
	accept_reasons: [
		{text: 'some reason', value: 'some reason'},
		{text: 'another reason', value: 'another reason'}
	],
	reject_reasons: [
		{text: 'You have not declared a conviction that is on your criminal record', value: 'You have not declared a conviction that is on your criminal record'},
		{text: 'The Declaration of Criminal Record is not complet', value: 'The Declaration of Criminal Record is not complet' },
		{text: 'The dates are incorrect on your Declaration of Criminal Record', value: 'The dates are incorrect on your Declaration of Criminal Record' }
	],
	accept_reject: 'accept',
	accept_reject_reason: '',
	is_equifax_validated: false,
	confirmed_result: false,
	negative_result: false,
	incomplete_result: false,
	bg_result_type: '',
	want_bg_check_copy: false,
	shipping_address: '',
	shipping_to: '',
	records_to_be_checked: [], // list of records to be checked for policemen.
	view_more_requests: false,
	is_convicted_crime: false,
	country: '',
	identify_id_countries: [],
	bg_check_type:'',
	dbo_validation: true,
	dos_validation_btn: true,
	dos_validation: [true],
	convictions: [
		{date: '',location:'',offence:''}
	],
	current_user: {},
	password_reset_email: '',
	text_mask: ['/^[a-zA-Z0-9!@#$%\^&*)(+=._-]*$/']
  }),
	async mounted(){
		this.update_total(false);
		// check if user is loggedin
		console.log('identify_countries');
		console.log(identify_countries);
		this.identify_id_countries = identify_countries;
		this.countries = identify_countries_values;
		this.getBgCheckLoggedUser().then(response => {
			if(response.user_type != 'bg_check') {
				this.logout();
			} else {
				console.log('response ......');
				console.log(response);
				this.is_logged_in = true;
				this.current_user = response;
				if(response.is_policeman == true) {
					this.is_policeman = true;
					this.police_step = 1;
					// check and load record.
					let id = (this.$route.query.id != undefined) ? this.$route.query.id : 0;
					this.getRecordsToBeVerifiedMethod();
					

				}
			}
		});
		
		/* check if user is loggeding. */
		let token = localStorage.getItem('bg_check_token');
		if(token != null) {
			this.update_step_flow();
			this.is_logged_in = true;
			if(this.is_policeman == false) {
				
				// check if user have unfinished record?
				this.checkIfHasUnfinishedBackgroundCheck();
			}
		} 
		
		this.getCountryProvinceListForBootstrap().then(response => {
			if(response.status == true ) {
				this.provice_state_of_birth = response.country_list.CA;
				this.current_provinces = response.country_list.CA;
				this.provinces = response.country_list.CA;
				this.ca_provinces = response.country_list.CA;
				this.us_provinces = response.country_list.US;
				console.log('this.us_provinces');
				console.log(this.us_provinces);
				this.ca_provinces.unshift({text: 'Select Region', value: ''});
				this.us_provinces.unshift({text: 'Select Region', value: ''});
				this.provice_state_of_birth = this.ca_provinces;
				this.current_provinces = this.ca_provinces;
				console.log('this.provice_state_of_birth');
				console.log(this.provice_state_of_birth);
				console.log('this.current_provinces');
				console.log(this.current_provinces);
			}
		});
		// assign reasons
		this.reasons = this.accept_reasons;
		this.getBgQuestions();
	},
  	methods: {
		...mapActions([
			"getBackgroundQuestions", "doBgCheckLogin", 'getBgCheckLoggedUser', 'doBgCheckRegister','doLogout',
			"updateBgStep",
			"updateBgStepCountOnly",
			"ifHasUnfinishedBackgroundCheck",
			"uploadImage",
			"uploadVideo",
			"getCountryProvinceListForBootstrap",
			"updateStepCountOnly",
			"makeBackgroundCheckPayment",
			"getBackgroundCheckByID",
			"acceptOrRejectBackgroundCheck",
			"uploadCertificateFileandComplete",
			"postEquifaxQuestions",
			"getRecordsToBeVerified",
			"updateConvictedCrim",
			"sendBgCheckPasswordResetLink",
			]),
		selectBrithCountry(value){
			//this.form.birth_country.selected_value = value.value;
			console.log('this.form.birth_country.selected_value');
			console.log(value);
			console.log(this.form.birth_country.selected_value);
			
			
		},
		verify_text_mask(e) {
			console.log('e.keyCode');
			console.log(e.keyCode);
			let char = String.fromCharCode(e.keyCode); // Get the character
			var arr = [33,34,35,36,37,38,39,40,41,43,44 ];
			if(arr.includes(e.keyCode)) {
				e.preventDefault(); // If not match, don't add to input text
			} else if(/^[A-Za-z -.]/.test(char)){
				console.log('passed');
				return true; // Match with regex 
			} else {
				e.preventDefault(); // If not match, don't add to input text
			}
		},
		verify_zip_mask(e) {
			let char = String.fromCharCode(e.keyCode); // Get the character
			var arr = [33,34,35,36,37,38,39,40,41,43,44 ];
			if(arr.includes(e.keyCode)) {
				e.preventDefault(); // If not match, don't add to input text
			} else if(/^[A-Za-z0-9]+$/.test(char)) {
				return true; // Match with regex 
			} else {
				e.preventDefault(); // If not match, don't add to input text
			}
		},

		validate_convic_date(){
			let t = this;
			let i = 0;
			let convic = [];
			let is_date_wrong = true;
			$('.date_of_sentense').each(function(){
				let dt = $(this).val();
				let dt_ret = t.validateDate(dt);
				if(dt_ret == false) {
					is_date_wrong = false;
				}
				console.log('dt_ret');
				console.log(dt_ret);
				convic[i] = dt_ret;
				i++;
			});
			this.dos_validation_btn = is_date_wrong;
			this.dos_validation = convic;
		},
		add_more_convictions(){
			let convic = {date: '',location:'',offence:''};
			this.dos_validation[this.convictions.length] = true;
			this.convictions.push(convic);
		},
		remove_convictions(){
			console.log('remove conviction ');
			let len = (this.convictions.length-1);
			let convictions = this.convictions;
			if(len >= 1) {
				convictions = _.filter(this.convictions, (rec, ind) => {
					if(ind < len) {
						return true;
					}
				});
				console.log(convictions);

			}
			console.log(convictions);
			this.convictions = convictions;
		},
		selectIdentificationCountry(value){
			this.form.issuing_country_reqion.selected_value = value.value;
		},
		validate_dob_date(selector) {
			if(selector == 'personal__dob') {
				let dt = this.form.date_of_birth_name.selected_value;
				console.log('personal__db_inner');
				let date1 = new Date(dt); 
				let date2 = new Date(); 
				
				// To calculate the time difference of two dates 
				let Difference_In_Time = date2.getTime() - date1.getTime(); 
				
				// To calculate the no. of days between two dates 
				let Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24); 
				let ret = this.validateDate(dt);
				if(ret == true) {
					if(Difference_In_Days < 6570) {
						this.showMessage('error', 'Error: You must be greater than 18 years old to proceed.')
						this.dbo_validation = false;
					} else {
						this.dbo_validation = ret;
					}
				} else {
					this.dbo_validation = ret;
				} 
			}
			
		},
		validate_sentence_date(selector, ind) {
			let dt = this.convictions[ind];
			let ret = this.validateDate(dt);
			this.dos_validation[ind] = ret;
			this.dos_validation_btn = ret;
			
		},
		validateDate(dateString) {

			// First check for the pattern
			if(!/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(dateString)) {
				return false;
			}

			// Parse the date parts to integers
			var parts = dateString.split("-");
			var year = parseInt(parts[0], 10);
			var month = parseInt(parts[1], 10);
			var day = parseInt(parts[2], 10);
			// Check the ranges of month and year
			if(year < 1900 || year > 2021 || month == 0 || month > 12) {
				return false;
			}

			var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

			// Adjust for leap years
			if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
				monthLength[1] = 29;

			// Check the range of the day
			return day > 0 && day <= monthLength[month - 1];
		},
		update_bg_check_fee(value){
			this.bg_check_type = value;
			this.fee = 49;
			this.update_total(false);
			this.form.bg_check_type.selected_value = value;
		},
		reset_bg_check_fee(value){
			this.bg_check_type = '';
			this.form.bg_check_type.selected_value = '';
		},
		switchBgRecord(id){
			if(id != this.form.id) {
				this.getBeckgroundCheckRecord({id: id, strict: true}); 
			}
		},
		getBeckgroundCheckRecord(payload) {
			this.getBackgroundCheckByID(payload).then(response => {
				if(response.status == true) {
					this.form = response.record;
					this.policeman_record = response.questions;
					this.convictions = this.policeman_record.convictions.selected_value;
					this.record_id = response.record.id;
					this.view_more_requests = false;
					
					// set verification ID country if already selected.
					let country_val = this.form.issuing_country_reqion.selected_value;
					_.map(this.identify_id_countries, (country_obj) => {
						if(country_val == country_obj.value) {
							this.country = country_obj;
						}
					});
					this.getRecordsToBeVerifiedMethod();
					
				} else {
					// show record not found error.
					this.showMessage('error', response.message);
				}
			});
		},
		getAddressData: function (addressData, placeResultData, id) {
			this.address = addressData;
			console.log(addressData);
			
			this.form.current_street_address.selected_value = ((addressData.street_number != undefined) ? addressData.street_number +" ": "")+addressData.route;
			this.form.current_country.selected_value = addressData.country;
			this.form.current_provice.selected_value = addressData.administrative_area_level_1;
			this.form.current_city.selected_value = addressData.locality;
			console.log(addressData.postal_code);
			this.form.current_postal.selected_value = (addressData.postal_code != undefined) ? addressData.postal_code.replace(/\s/g, '') : '';
			
		},
		getAddressData_Delivery: function (addressData, placeResultData, id) {
			this.shipping_address = placeResultData.formatted_address;
		},
		getRecordsToBeVerifiedMethod() {
			if(this.is_policeman == true) {
				this.getRecordsToBeVerified().then(response => {
					if(response.status == true) {
						this.records_to_be_checked = response.records;
						this.view_more_requests = true;
					} else {
						// show record not found error.
						this.showMessage('error', response.message);
					}
				});
			}
		},
		view_more_requests_method(){
			this.view_more_requests = !this.view_more_requests;
		},
		changeBGresultText() {
			this.confirmed_result = false;
			this.negative_result = false;
			this.incomplete_result = false;
			if(this.bg_result_type == 'Confirmed Declaration') {
				this.confirmed_result = true;
			} else if(this.bg_result_type == 'Incomplete') {
				this.incomplete_result = true;
			} else if(this.bg_result_type == 'Negative') {
				this.negative_result = true;
			}


		},
		accept_reject_value(value) {
			if(value == 'accept') {
				this.reasons = this.accept_reasons;
			} else {
				this.reasons = this.reject_reasons;
			}
		},
		getBgQuestions(){
			this.getBackgroundQuestions().then(response => {
				if(response.status == true) {
					this.form = response.result;
					if(this.form.birth_country.selected_value == '') {
						this.form.birth_country.selected_value = 'Select Country';
					}
					if(this.form.current_country.selected_value == '') {
						this.form.current_country.selected_value = 'Select Country';
					}
					if(this.form.issuing_country_reqion.selected_value == '') {
						this.form.issuing_country_reqion.selected_value = 'Select Country';
					}
					
				}

			});
		},
		show_application_submission() {
			console.log('hiiiiiiiiiiiiii');
			this.police_step = 2;
			this.scrollToTop();
			console.log('hiiiiiiiiiiiiii');
		},
		hide_application_submission() {
			this.police_step = 1;
			this.scrollToTop();
		},
		
	checkIfHasUnfinishedBackgroundCheck(){
		// check if user have unfinished record?
		if(this.is_policeman == false) {
			this.ifHasUnfinishedBackgroundCheck().then(resp => {
				if(resp.response == true) {
					if(resp.record != null) {
						
						this.has_unfinished_record = true;
						this.unfinished_record = resp.questions;
						this.unfinished_bg_record = resp.record;
						this.is_convicted_crime = resp.record.convicted_crime;
						this.if_unfinished_record_exist = true;
						console.log('unfinished bg record');
						console.log(this.unfinished_bg_record);
					}
				}

			});
		}
	},
	logout(){
		localStorage.removeItem('bg_check_token');
		this.current_step = 1;
		this.last_completed_step = 1;
		this.has_unfinished_record = false;
		this.auth_form_type = 'login';
		this.progress = 0;
		this.is_policeman = false;
		this.is_logged_in = false;
		this.error = false;
		this.success = false;
	},
    
    goToStep(step) {
		this.current_step = step;
	},
    goForNextStep() {
		
	},
	formatFileNames(files) {
      return files[0].name;
	},
	mapImageValues(values) {
	  	let formData = new FormData();
      	formData.append("file_name", values.file_name);
      	formData.append("file_value", values.file);
      	formData.append("id", this.record_id);
	  	formData.append("current_step", this.current_step);
	  	formData.append("question_id", values.question_id);
	  	formData.append("last_completed_step", this.last_completed_step);
	  	formData.append("check_result", this.bg_result_type);
	    return formData;
    },

    goForCrimeStep() {
      this.current_step = this.current_step + 1;
      console.log("form submitted.");
      console.log(this.current_step);
    },
    goToNextStep() {
		console.log('this.is_convicted_crime');
		console.log(this.is_convicted_crime);
		console.log(this.current_step);
		if(this.current_step == 5 && this.is_equifax_validated == true) {
			// jump to step 9
			this.current_step = 9;
		} else if(this.is_convicted_crime == false && this.current_step == 10) {
			this.current_step = 12;
			console.log('next in convicted_crime');
		} else if(this.current_step == 5 && this.is_equifax_validated == false) {
			this.current_step = 7;
		} else if(this.current_step < this.last_completed_step) {
			this.current_step = this.current_step + 1;
		} else {
			console.log('next in else');
		}
    },
    goToPrevStep() {
		console.log('this.is_convicted_crime');
		console.log(this.is_convicted_crime);
		console.log(this.current_step);
		if(this.current_step == 9 && this.is_equifax_validated == true) {
			// jump to step 6
			this.current_step = 5;
		} else if(this.current_step == 7 && this.is_equifax_validated == false) {
			// jump to step 6
			this.current_step = 5;
		} else if(this.is_convicted_crime == false && this.current_step == 12) {
			this.current_step = 10;
		} else if(this.current_step > 2) {
			this.current_step = this.current_step - 1;
		}
      
    },
    goForNoCrimeStep() {
      this.current_step = this.current_step + 1;
      console.log("form submitted.");
      console.log(this.current_step);
    },
    update_step_flow() {
		console.log('inside update step flow');
		console.log(this.current_step);
		if(this.current_step == 6 && this.is_equifax_validated == true) {
			// jump to step 9
			this.current_step = 9;
		} else if(this.current_step == 5 && this.is_equifax_validated == false) {
			this.current_step = this.current_step + 2;
		} else {
			// jump
			this.current_step = this.current_step + 1;
		}

		
		this.last_completed_step = (this.current_step <= this.last_completed_step) ? this.last_completed_step : this.current_step;
      	console.log("form submitted.");
      	console.log(this.current_step);
		this.update_progress_bar();
		this.scrollToTop();
	},
    update_step_count(step) {
		this.current_step = this.current_step + 1;
		let payload = {};
		payload.id = this.record_id;
		payload.current_step = step;
		this.updateStepCountOnly(payload).then(resp => {

		});
		this.update_progress_bar();
	},
	update_progress_bar(){
		let progress = Math.round((this.current_step / this.steps) * 100);
		if (progress < 100) {
			this.progress = progress;
		} else {
			this.progress = 100;
		}
	},
	
    resetMessages() {
		this.error = false;
		this.error_message = '';
		this.success = false;
		this.success_message = '';
	},
	async sendBgRequest(payload) {
		payload.id = this.record_id;
		console.log('steps');
		console.log(this.current_step);
		console.log(this.last_completed_step);
		payload.current_step = this.current_step;
		console.log('this.last_completed_step');
		console.log(this.last_completed_step);
		payload.last_completed_step = this.last_completed_step;
		payload.form = _.map(_.filter(payload.form, (rec, index) => { return payload.fields.indexOf(index) > -1 ? rec: false;}), (rec, index) => {
			console.log('rec');
			console.log(rec)
			if(rec.field_id == 'birth_country') {
				rec.selected_value = rec.selected_value.value != undefined ? rec.selected_value.value : rec.selected_value;
			} else if(rec.field_id == 'current_country') {
				rec.selected_value = rec.selected_value.value != undefined ? rec.selected_value.value : rec.selected_value;
			} 
			return rec;
		});
		await this.updateBgStep(payload).then(resp => {
			console.log('update bg step RESPONSE');
			console.log(resp);
			if(resp.response) {
				if(this.current_step == 5) { // resturn back to handle equifax response
					console.log('inside before if');
					this.make_equifax_response(resp);
				} else {
					console.log('before else');
					console.log(resp);
					this.record_id = resp.id;
					this.update_step_flow();

				}
			} else {
				this.showMessage('error', resp.error_message);
			}
			this.scrollToTop();
		});
	},
	resume_unfinished_record(){
		console.log('resume unfinished');
		// set birth province for CA or US to make it preselected.
		this.dos_validation = [];
		this.provice_state_of_birth = (this.unfinished_record.birth_country.selected_value == 'CA') ? this.ca_provinces : this.us_provinces;
		this.current_provinces = (this.unfinished_record.current_country.selected_value == 'CA') ? this.ca_provinces : this.us_provinces;
		this.provices = this.us_provinces;
		this.form = this.unfinished_record; // questions 
		this.current_step = this.unfinished_bg_record.current_step;
		console.log('this.current_step' );
		console.log(this.current_step );
		console.log(this.provice_state_of_birth );
		this.convictions = (this.unfinished_record.convictions.selected_value == '') ? this.convictions : this.unfinished_record.convictions.selected_value;
		
		this.last_completed_step = this.current_step;
		this.record_id = this.unfinished_bg_record.id;
		this.if_unfinished_record_exist = false;
		// set default country if empty 
		if(this.unfinished_record.birth_country.selected_value == '') {
			this.unfinished_record.birth_country.selected_value = 'Select Country';
		}
		if(this.unfinished_record.current_country.selected_value == '') {
			this.unfinished_record.current_country.selected_value = 'Select Country';
		}
		if(this.unfinished_record.issuing_country_reqion.selected_value == '') {
			this.unfinished_record.issuing_country_reqion.selected_value = 'Select Country';
		}
		
		// set it to false if it was previously selected.
		this.is_equifax_validated = (this.unfinished_bg_record.equifax_status == 1) ? true: false;
		this.bg_check_type = this.form.bg_check_type.selected_value;
		console.log(this.bg_check_type);
		console.log('this.bg_check_type');
		if(this.bg_check_type == 'police_criminal_records') {
			this.fee = 39;
		} else if(this.bg_check_type == 'police_criminal_and_judicial_records') {
			this.fee = 49;
		} else {
			this.fee = 39;
		}
		this.update_total(false);
		// set verification ID country if already selected.
		let country_val = this.form.issuing_country_reqion.selected_value;
		_.map(this.identify_id_countries, (country_obj) => {
			if(country_val == country_obj.value) {
				this.country = country_obj;
			}
		});
		
		this.update_progress_bar();
	},
	checkFormErrors() {
		let error = false;
		$('.active_step .required').each((function() {
			$(this).removeClass('error');
			if($(this).hasClass('custom-checkbox')) {
				if($(this).find('.custom-control-input').val() == "") {
					error = true;	  
					$(this).addClass('error');
				}
			} else if($(this).val() == '') {
				error = true;	  
				$(this).addClass('error');
			}
		}));
		return error;
	},
    step_one() {
      	
    },
    step_two() {
		let error = this.checkFormErrors();
		let name = (this.current_user.first_name+' '+this.current_user.last_name).toLowerCase();
		let initial = this.form.customer_initial.selected_value.toLowerCase();
		console.log('name');
		console.log(name);
		console.log(initial);
	  	if(error == true) {
			this.showMessage('error', 'Please provide all required fields.');
		} else if(name != initial) {
			this.showMessage('error', 'Please provide valid initial.');
		} else {
			// send update request
			let fields = [];
			if(this.bg_check_type == 'police_criminal_and_judicial_records') {
				fields = [
					"criminal_offence_individual_convicted",
					"finding_guilt_under_ycja",
					"criminal_offence_individual_found_guilty",
					"criminal_offence_outstanding_charge",
					"court_order_against_individual",   
					"condition_which_pardon_suspension",
					"customer_initial",
					"bg_check_type",
				];
			} else {
				 fields = [
					"search_ofrcmp_national_repo",
					"understand_varification_process",
					"acknowledge_that_globeia_is_thirdparty",
					"search_of_police_info",
					"customer_initial",
					"bg_check_type",
				];
			}
			this.sendBgRequest({fields: fields, form: this.form});

		}
    },
    step_three() {
      	let error = this.checkFormErrors();
	  	if(error == true) {
			this.showMessage('error', 'Please provide all required fields.');
		} else {
			// send update request
			let fields = [
				"legal_first_name",
				"middle_name",
				"legal_lasst_name",
				"former_name",
				"date_of_birth_name",
				"select_your_sex",
				"phone_number",
				"accept_privacy_policy",
				"forever_discharge_all_members",
			];
			this.sendBgRequest({fields: fields, form: this.form});

		}
      
    },
    step_four() {
		let error = this.checkFormErrors();
	  	if(error == true) {
			this.showMessage('error', 'Please provide all required fields.');
		} else if(this.form.birth_country.selected_value == 'Select Country') {
			this.showMessage('error', 'Please select a valid Country.');
		} else {
			// send update request
			let fields = [
				"birth_country",
				"provice_state_of_birth",
				"city_municipalit_of_birth",
			];
			this.sendBgRequest({fields: fields, form: this.form});

		}
    },
    step_five() {
      let error = this.checkFormErrors();
	  	if(error == true) {
			this.showMessage('error', 'Please provide all required fields.');
		} else if(this.form.current_country.selected_value == 'Select Country') {
			this.showMessage('error', 'Please select a valid Country.');
		} else {
			// send update request
			let fields = [
				"current_address_lookup",
				"current_street_address",
				"unit_number",
				"current_country",
				"current_provice",
				"current_city",
				"current_postal",
				"legal_first_name",
				"middle_name",
				"legal_lasst_name",
				"date_of_birth_name",
			];
			this.sendBgRequest({fields: fields, form: this.form});

		}
	},
	make_equifax_response(resp) {
		console.log('insde equifax_respose');
		console.log(resp);
		
		if(resp.check_status == true) { //validation successful, now show questions
			this.equifax_questions = resp.question;
			this.equifax_transaction_id = resp.equifax_transaction_id;
			this.interactive_query_id = resp.interactive_query_id;
			this.current_step = this.current_step+1;
			this.update_progress_bar();
			this.scrollToTop();
		} else { //validation was not successful, store transaction id and reason for summary.
			console.log(this.current_step);
			this.is_equifax_validated = false;
			this.current_step = this.current_step+2;
			this.update_progress_bar();
			this.scrollToTop();
		}
	},
	step_six() {
		console.log('equifax_temp_answers');
		console.log(this.equifax_temp_answers);
		let payload = {};
		payload.answers = this.equifax_temp_answers;
		payload.interactive_query_id = this.interactive_query_id;
		payload.equifax_transaction_id = this.equifax_transaction_id;
		payload.record_id = this.record_id;
		this.postEquifaxQuestions(payload).then(resp => {
			if(resp.response == true) {
				this.is_equifax_validated = true;
				this.update_step_flow();
				
			} else {
				this.showMessage('error', resp.messaage);
			}
		});
		
      
    },
    step_seven() {
		let error = this.checkFormErrors();
	  	if(error == true) {
			this.showMessage('error', 'Please provide all required fields.');
		} else if(this.form.issuing_country_reqion.selected_value == 'Select Country') {
			this.showMessage('error', 'Please select a valid Country.');
		} else {
			// send update request

			let fields = [
				"issuing_country_reqion",
				"select_id_type",
			];
			this.sendBgRequest({fields: fields, form: this.form});
			
		}
      
	},
	upload_ID_front(){
		setTimeout(()=>{
			let fields = [
				"front_picture_of_id_1",
			];
			console.log(this.front_picture_of_id_1);
			console.log('this.front_picture_of_id_1');
			let question_id = 0;
				_.map(_.filter(this.form, (rec, index) => { console.log(index); return fields.indexOf(index) > -1 ? rec: false;}), (rec, index) => {
					question_id = rec.id;
			});
			let data = {'file_name': 'front_picture_of_id_1', 'file': this.front_picture_of_id_1, 'question_id': question_id};
			let formData = this.mapImageValues(data);
			this.uploadImage(formData).then(resp => {
				console.log('update bg step RESPONSE');
				console.log(resp);
				if(resp.response) {
					this.form.front_picture_of_id_1.selected_value = resp.url;
					this.front_picture_of_id_1 = [];
				} else {
					this.showMessage('error', resp.message);
				}
				this.scrollToTop();
			});
		}, 1000);
	},
	completeBackgroundCheckRequest(){
		console.log('submitted');
		setTimeout(()=>{
			let fields = [
				"certificate_file",
			];
			console.log(this.certificate_file);
			console.log('this.certificate_file');
			let question_id = 0;
				_.map(_.filter(this.policeman_record, (rec, index) => { console.log(index); return fields.indexOf(index) > -1 ? rec: false;}), (rec, index) => {
					question_id = rec.id;
			});
			let data = {
				'file_name': 'certificate_file', 
				'file': this.certificate_file, 
				'question_id': question_id
				};
			let formData = this.mapImageValues(data);
			this.uploadCertificateFileandComplete(formData).then(resp => {
				if(resp.response) {
					this.policeman_record.certificate_file = resp.url;
					this.getRecordsToBeVerifiedMethod();
					this.record_id = 0;
					this.bg_result_type = '';
					this.confirmed_result = false;
					this.negative_result = false;
					this.incomplete_result = false;
					this.showMessage('success', resp.message);
				} else {
					this.showMessage('error', resp.error_message);
				}
				this.scrollToTop();
			});
		}, 1000);
	},
	upload_ID_back(){
		setTimeout(() => {
			let fields = [
				"back_picture_of_id_1",
			];
			console.log(this.back_picture_of_id_1);
			console.log('this.back_picture_of_id_1');
			let question_id = 0;
				_.map(_.filter(this.form, (rec, index) => { console.log(index); return fields.indexOf(index) > -1 ? rec: false;}), (rec, index) => {
					question_id = rec.id;
			});
			let data = {'file_name': 'back_picture_of_id_1', 'file': this.back_picture_of_id_1, 'question_id': question_id};
			let formData = this.mapImageValues(data);
			this.uploadImage(formData).then(resp => {
				console.log('update bg step RESPONSE');
				console.log(resp);
				if(resp.response) {
					this.form.back_picture_of_id_1.selected_value = resp.url;
					this.back_picture_of_id_1 = [];
				} else {
					this.showMessage('error', resp.message);
				}
				this.scrollToTop();
			});
		}, 1000);
	},
    step_eight() {
      let fields = [
			"record_short_video",
		];
		let question_id = 0;
			_.map(_.filter(this.form, (rec, index) => { console.log(index); return fields.indexOf(index) > -1 ? rec: false;}), (rec, index) => {
				question_id = rec.id;
		});
		let data = {'file_name': 'record_short_video', 'file': this.record_short_video, 'question_id': question_id};
		let formData = this.mapImageValues(data);
		this.uploadVideo(formData).then(resp => {
			console.log('update bg step RESPONSE');
			console.log(resp);
			if(resp.response) {
				this.form.record_short_video.selected_value = resp.url;
				this.update_step_flow();
				this.record_short_video = [];
			} else {
				this.showMessage('error', resp.message);
			}
			this.scrollToTop();
		});
    },
	convicted_crime(){
		this.is_convicted_crime = true;
		let payload = {};
		payload.id = this.record_id;
		payload.current_step = this.last_completed_step;
		payload.status = 1;

		this.updateConvictedCrim(payload).then(resp => {
			if(resp.response == true) {
				this.update_step_flow();
			} else {
				this.showMessage('error', resp.message);
			}
		});
			
	},
	async not_convicted_crime() {
		console.log('this.last_completed_step');
		console.log(this.last_completed_step);
		this.is_convicted_crime = false;
		let step = (this.last_completed_step >= 12) ? this.last_completed_step : 12;
		let payload = {};
		payload.id = this.record_id;
		payload.current_step = step;
		payload.convicted_crime = this.is_convicted_crime;
		this.convictions = [
			{date: '',location:'',offence:''}
		];
		await this.updateBgStepCountOnly(payload).then(resp => {
			console.log('update bg step RESPONSE');
			console.log(resp);
			if(resp.response) {
				this.current_step = 12;
				this.update_progress_bar();
			} else {
				this.showMessage('error', resp.message);
			}
		});
	},
    step_nine() {
	  	this.update_step_flow();
	  
    },
    step_ten() {
      this.update_step_flow();
    },
    step_eleven() {
      	let fields = [
			  'convictions'
		];
		this.form.convictions.selected_value = this.convictions;
		this.sendBgRequest({fields: fields, form: this.form});
    },
    step_twelve() {
      this.update_step_count(13);
    },
    step_thirteen() {
		console.log(this.card);
		let payload = this.card;
		if(this.card.country == 'Select Country') {
			this.showMessage('error', 'Please make sure you have selected correct country.');
		} else {

		
			if(payload.country != undefined) {
				console.log('not undefined.');
				payload.country = payload.country.code;
				payload.id = this.record_id;
				payload.current_step = this.current_step;
				payload.shipping_method = this.shipping_method;
				payload.shipping_amount = this.shipping.shipping_amount;
				payload.want_bg_check_copy = this.want_bg_check_copy;
				payload.total_fee = this.total_fee;
				payload.shipping_address = this.shipping_address;
				payload.shipping_to = this.shipping_to;
				
				this.makeBackgroundCheckPayment(payload).then(resp => {
					console.log('resp');
					console.log(resp);
					if(resp.status == true) {
						this.progress = 100;
						this.is_completed = true;
						this.convictions = [
							{date: '',location:'',offence:''}
						];
						this.form = {};
						this.card = {
							name_on_card: '',
							street_address: '',
							postal: '',
							country: '',
							outline: '',
							province: '',
							cc_number: '',
							exp_date: '',
							cvd: '',
						};
						this.showMessage('success', resp.message);
						// show thank you  page.
						this.current_step = 0;
					} else {
						this.showMessage('error', resp.message);
					}
				});
			} else {
				this.showMessage('error', 'Please make sure you have selected correct country.');
			}
		}
    },
    step_fourteen() {
      this.update_step_flow();
    },
    step_fifteen() {
      this.update_step_flow();
    },
    handleSubmit(event) {
		this.resetMessages();
		event.preventDefault();
		console.log('this.form');
		console.log(this.form);
		console.log(this.current_step);
		switch (this.current_step) {
			case 1:
				this.step_one();
			break;
			case 2:
				this.step_two();
			break;
			case 3:
				this.step_three();
			break;
			case 4:
				this.step_four();
			break;
			case 5:
				this.step_five();
			break;
			case 6:
				this.step_six();
			break;
			case 7:
				this.step_seven();
			break;
			case 8:
				this.step_eight();
			break;
			case 9:
				this.step_nine();
			break;
			case 10:
				this.step_ten();
			break;
			case 11:
				this.step_eleven();
			break;
			case 12:
				this.step_twelve();
			break;
			case 13:
				this.step_thirteen();
			break;
			case 14:
				this.step_fourteen();
			break;
			case 15:
				this.step_fifteen();
			break;
			default:
      }
	},
	switchAuthForm(type){
		switch(type){
			case 'login':
				this.auth_form_type = 'login';
			break;
			case 'register':
				this.auth_form_type = 'register';
			break;
			case 'forget_pass':
				this.auth_form_type = 'forget_pass';
			break;
			default:
		}
	},
	doLogin() {
		console.log(this.login);

		this.error = false;
		this.error_message = '';
		if(!this.validateEmail(this.login.email)) {
			this.showMessage('error', 'please provide valid email address');
		} else {
			this.doBgCheckLogin({username: this.login.email, password: this.login.password, 'type': 'bg_check'}).then(response => {
				if(response.access_token) {
					this.getBgCheckLoggedUser().then(response => {
						console.log('response.user_type');
						console.log(response.user_type);
						if(response.user_type != 'bg_check') {
							this.showMessage('error', 'Please provide valid credentials.');
							this.doLogout();
						} else {
							this.is_logged_in = true;
							this.checkIfHasUnfinishedBackgroundCheck();
							this.update_step_flow();
							this.getBgQuestions();
							if(response.is_policeman == true) {
								this.is_policeman = true;
								this.getRecordsToBeVerifiedMethod();
								this.police_step = 1;
								
								this.record_id = 0; // reset form if was set previously.
								this.is_completed = true; // hide next-prev buttons
							} else {
								this.bg_check_type = '';
								this.dos_validation = [];
								this.is_completed = false; // show next/prev buttons
							}
						}
					});
					
				} else {
					this.error = true;
					this.error_message = response.error;
				}
			});
		}
	},
	validateEmail(email) {
		const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	},
	validatePassword(password) {
		const re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z0-9]{6,}$/;
		return password.match(/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])([a-zA-Z0-9]{6,})$/);
	},
	doRegister() {
		this.error = false;
		this.error_message = '';
		console.log('email validation');
		console.log(this.validatePassword(this.register.password));
		console.log(this.register.password);
		console.log(this.register.cpassword);
		if(!this.validateEmail(this.register.email)) {
			this.showMessage('error', 'please provide valid email address');
		} else if(this.register.password != this.register.cpassword) {
			this.showMessage('error', 'Password and confirm password should match.');
		} else if(!this.validatePassword(this.register.password)) {
			this.showMessage('error', 'Password should be a minimum 6 character with one uppercase and one number.');
		} else {
			this.doBgCheckRegister(this.register).then(response => {
				console.log('register response');
				console.log(response);
				if(response.status) {
					this.auth_form_type = 'login';
					this.login.email = '';
					this.login.password = '';
					this.register.first_name = '';
					this.register.last_name = '';
					this.register.email = '';
					this.register.password = '';
					this.register.cpassword = '';
					this.showMessage('success', response.message);
				} else {
					this.showMessage('error', response.message);
				}
			});
		}
	},
	doBgCheckPasswordReset() {
		this.error = false;
		this.error_message = '';
		this.sendBgCheckPasswordResetLink({email: this.password_reset_email}).then(response => {
			console.log('register response');
			console.log(response);
			if(response.status) {
				this.password_reset_email = '';
				
				this.showMessage('success', response.message);
			} else {
				this.showMessage('error', response.message);
			}
		});
	},
	scrollToTop(){
		setTimeout(() => {
			let selector = ($('.alert_wrapper').length == 1) ? $('.alert_wrapper') : $('.online_bg_step');
			console.log('selector');
			console.log(selector);
			let top_offset = selector.offset().top;
			$('html, body').animate({
				scrollTop: top_offset
			}, 500);
		}, 100);
	},
	onCountryChange() {
      var ctr = this.card.country;
      console.log(ctr);
      console.log(this.ca_provinces);
      console.log(this.us_provinces);
      if(ctr == 'CA') {
        this.provice_state_of_birth = this.ca_provinces;
      } else {
        this.provice_state_of_birth = this.us_provinces;
      }
    },
	onCountryChangeBySearchable(e) {
      console.log(e);
    },
	getBirthCountryStates(ctr) {
      console.log(ctr);
      console.log(this.ca_provinces);
      console.log(this.us_provinces);
      if(ctr == 'CA') {
        this.provice_state_of_birth = this.ca_provinces;
      } else {
        this.provice_state_of_birth = this.us_provinces;
      }
    },
	getCurrentCountryStates(ctr) {
      console.log(ctr);
      console.log(this.ca_provinces);
      console.log(this.us_provinces);
      if(ctr == 'CA') {
        this.current_provinces = this.ca_provinces;
      } else {
        this.current_provinces = this.us_provinces;
      }
    },
	update_shipping(e) {
		if(e == 10) {
			this.shipping.text = 'Canada Post non-trackable mail';
			this.shipping.shipping_amount = 10;
			this.total_fee = (this.fee+this.shipping.shipping_amount);
		} else if(e == 30) {
			this.shipping.text = 'Expedited (1-2 Day Courier) Within Canada';
			this.shipping.shipping_amount = 30;
			this.total_fee = (this.fee+this.shipping.shipping_amount);
		} else {
			this.shipping.text = 'I want to send this via international courier - (we will contact you)';
			this.shipping.shipping_amount = 0;
			this.total_fee = this.fee;
		}

		this.update_total(true);
		this.scrollToTop();
	},
	update_total(value) {
		this.want_bg_check_copy = (value == true) ? true : false;
		if(this.want_bg_check_copy == true) {
			this.subtotal = (this.fee+this.shipping.shipping_amount);
		} else {
			this.subtotal = (this.fee);
		}
		
		this.tax_amount = ((this.tax/100)*this.subtotal);
		this.total_fee = (this.subtotal+this.tax_amount);
	},
	showMessage(type = 'error', message) {
		if(type == 'success') {
			this.success = true;
			this.success_message = message;
			this.error_message = '';
			setTimeout(()=> {
				this.success = false;
				this.success_message = '';	
			}, 10000);
		} else {
			this.error = true;
			this.error_message = message;
			this.success_message = '';
		}
		this.scrollToTop();
	},
  },
};
</script>