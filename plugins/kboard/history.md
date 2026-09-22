#KBoard Changelog

[homepage](https://www.cosmosfarm.com/products/kboard)

6.8
----------------------------------

  1. 전체 게시글 페이지에서 일괄로 '승인 대기중', '발행됨' 상태 변경 기능 추가
  2. 일부 테마 사용 시 기본 제공 스킨의 게시글 본문 화면에서 인쇄 버튼과 첨부파일 다운로드 버튼 글자색이 올바르게 표시되도록 개선
  3. 게시판 category3~5 게시글 수 집계 오류 수정
  4. 게시글 본문에서 유튜브/비메오 URL 자동 영상 변환 및 자동링크 처리 오류 수정
  5. 게시판 검색 및 콘텐츠 처리 안정화, 접근 권한 검증 보완
  6. 작성자 IP 저장형 XSS, 게시글 옵션 객체 주입, 관리자 시스템 옵션 CSRF 취약점 보완
  7. 썸네일 URL 출력 이스케이프 보강
  8. 검색 인덱스와 기존 와일드카드 검색의 대상, 연산자, 필터 및 정렬 결과 동기화
  9. 관리자 전체 게시글 목록에 선택 게시글 영구 삭제 일괄 동작 추가
  10. 관리자 전체 댓글 목록에 표시 개수 선택 기능 추가 및 검색·날짜 필터와 페이지 이동 상태 유지
  11. Google reCAPTCHA 비활성화 옵션 추가 및 기존 v2·v3 설정 호환성 유지
  12. 게시글 상태 변경 및 날짜 수정 시 다른 게시글 날짜가 변경되거나 1970-01-01로 저장되는 문제 수정
  13. 게시글 삭제 요청을 POST로 제한하여 프리로드·GET 요청에 의한 의도하지 않은 삭제 방지


6.7.1
----------------------------------

  1. 입력 필드 드래그 이동 시 설정값이 유실되는 버그 수정
  2. 게시글 본문 표시하기 설정 초기화 버그 수정


6.7
----------------------------------

  1. kboard_new_comment_alerts_subject 필터 추가
  2. kboard_new_comment_alerts_message 필터 추가
  3. kboard_url_to_string 지원 도메인 확대(유튜브 임베드)
  4. 기본 내장 스킨 디자인 개선
  5. 작성자 숨기기 관리자 제외 옵션 추가
  6. 사이드톡 연동 기능 추가
  7. 신규 댓글 알림 기능 추가
  8. 작성자명 저장 방법 기능 추가
  9. 댓글 작성자명 저장 방법 기능 추가
  10. 기간 내 게시글만 표시하기 기능 추가
  11. 최신글 리스트에 추가로 표시할 정보 기능 추가
  12. 작성자 활동 보기 설정 추가
  13. 관리자 페이지 전체 게시글 기간 설정 필터 추가
  14. 관리자 페이지 전체 댓글 기간 설정 필터 추가
  15. 관리자 답글 알림 제외
  16. csv 파일 다운로드 입력 필드 데이터 내용에 삽입 기능 추가
  17. 공지사항 만료 기간 기능 추가
  18. 카테고리 탭 추가 카테고리 기본 설정 카테고리 탭으로 이동
  19. 입력필드 탭에서 게시글본문 표시하기 설정 셀렉트문으로 개선
  20. 계층형 카테고리 블록테마와 호환 가능하게 개선
  21. 구글 reCAPTCHA v3 옵션 추가
  22. 게시글 비밀번호 저장 및 확인 처리 보안 강화
  23. SVG 업로드 보안을 강화
  24. 기존 SVG 파일을 점검·정리하는 도구를 추가
  25. 미디어 업로드/삭제 요청에 권한 검증 추가
  26. 헷갈리는 설정에 자세한 설명 문구 추가
  27. 대시보드 페이지에 공공데이터포탈 API 기능 추가
  28. 공지사항 리스트에 표시 설정 추가
  29. 버그 수정


6.6
----------------------------------

  1. 대량관리 CSV 파일 다운로드 기능 취약점 개선
  2. 버그 수정


6.5
----------------------------------

  1. kboard_url_to_string 필터 추가
  2. 게시글 표시 수 제외 옵션 추가
  3. 대시보드 페이지에 검색엔진 항상 읽기 가능 기능 추가
  4. 최신글 숏코드에 private 옵션 추가 (본인의 글만 최신글에 표시)
  5. 번역 파일 로드 방식 변경
  6. 버그 수정


6.4
----------------------------------

  1. kboard_list_rpp 필터 추가
  2. kboard_latest_select_count 필터 추가
  3. kboard_latest_select 필터 추가
  4. kboard_latest_from 필터 추가
  5. kboard_latest_where 필터 추가
  5. kboard_latest_orderby 필터 추가
  6. 회원 본인의 글만 표시하는 숏코드 추가 [kboard_my_list]
  7. kboard_autolink 도메인 지원 확대
  8. KBoard 자체 SEO 기능 최적화 코드 수정 (All In One SEO Pack, Rank Math 플러그인 대응)
  9. Summernote 에디터 사진 입력버튼을 비디오 입력 버튼으로 변경
  10. 인기글 활성화시 인기글 리스트에 공지사항 제거
  11. 버그 수정


6.3
----------------------------------

  1. kboard_popular_list_select 필터 추가
  2. kboard_popular_list_from 필터 추가
  3. kboard_popular_list_where 필터 추가
  4. kboard_popular_list_orderby 필터 추가
  5. kboard_content_get_class 필터 추가
  6. kboard_get_list_total 필터 추가
  7. kboard_vote_filter 필터 추가
  8. 전체 검색시 검색어에 작성자 포함 (대시보드에서 설정 가능)
  9. 전체 게시글 목록 개선 (휴지통 글은 섞이지 않도록 개선)
  10. 비밀글 강제 설정 추가
  11. 좋아요/싫어요 버튼 숨기기 기능 추가
  12. 본문/리스트 작성자 이름 숨기기 설정 추가
  13. 게시글 기본 정렬 순서 설정 추가
  14. 게시글의 카테고리 이름 일괄 바꾸기 기능 추가
  15. 게시판 리스트에 인기글 표시 설정 추가
  16. 최신글 숏코드 기능 추가
  17. 카테고리3, 카테고리4, 카테고리5 추가
  18. 버그 수정


6.2
----------------------------------

  1. 아이프레임으로 보기 관련 기능 개선
  2. 버그 수정


6.1
----------------------------------

  1. kboard_admin_content_list_table_columns 필터 추가
  2. kboard_admin_content_list_table_custom_column 액션 추가
  3. kboard_init_category1_list 필터 추가
  4. kboard_init_category2_list 필터 추가
  5. kboard_notice_list_select 필터 추가
  6. kboard_notice_list_from 필터 추가
  7. kboard_notice_list_where 필터 추가
  8. kboard_prevent_copy_text 필터 추가
  9. 복사 방지 기능 추가 (대시보드에서 설정 가능)
  10. kboard_xssfilter 개선 (font 태그 color 속성 추가)
  11. 포인트 적용 대상 설정 추가
  12. administrator 역할(role)에 manage_kboard 권한 추가
  13. 입력필드 게시글 본문에 표시 코드 개선
  14. 입력필드 설정에 새로운 확장 필드 추가 (이메일, 주소)
  15. 게시글 표시 수 관련 개선
  16. 버그 수정


6.0
----------------------------------

  1. kboard_document_insert_{$board_id} 액션 추가
  2. kboard_reply_insert 액션 추가
  3. kboard_reply_insert_{$board_id} 액션 추가
  4. kboard_order_nonmember_key 필터 추가
  5. kboard_multi_line_fields_fields 필터 추가
  6. kboard_url() 함수 추가
  7. kboard_get_user_total_count() 함수 추가
  8. kboard_comments_get_user_total_count() 함수 추가
  9. 썸머노트(Summernote) 에디터 최신 버전 적용
  10. KBoard 미디어 추가 경로 관련 코드 개선
  11. 고급설정에 우커머스 상품 탭에 표시 기능 추가
  12. 입력필드 설정에 새로운 확장 필드 추가 (HTML, 숏코드, 날짜 선택, 시간 선택)
  13. 버그 수정


5.9.2
----------------------------------

  1. KBContent 클래스에 getNextUID() 메소드 버그 수정
  2. 버그 수정


5.9.1
----------------------------------

  1. 첨부파일 관련 버그 수정


5.9
----------------------------------

  1. kboard_content_next_uid_query 필터 추가
  2. kboard_content_prev_uid_query 필터 추가
  3. kboard_content_option_value 필터 추가
  4. 이전 다음 게시글 가져오기 관련 코드 개선
  5. 첨부파일 경로 관련 코드 개선
  6. 첨부파일 다운로드, 삭제 주소 개선
  7. 대용량 첨부파일 다운로드 관련 개선
  8. kboard_board_attached 테이블 file_path 데이터형 varchar(255)로 변경
  9. 게시글의 답글 개수를 반환하는 코드 개선
  10. 스토어 로그인 관련 개선
  11. 버그 수정


5.9
----------------------------------

  1. kboard_content_next_uid_query 필터 추가
  2. kboard_content_prev_uid_query 필터 추가
  3. kboard_content_option_value 필터 추가
  4. 이전 다음 게시글 가져오기 관련 코드 개선
  5. 첨부파일 경로 관련 코드 개선
  6. 첨부파일 다운로드, 삭제 주소 개선
  7. 대용량 첨부파일 다운로드 관련 개선
  8. kboard_board_attached 테이블 file_path 데이터형 varchar(255)로 변경
  9. 게시글의 답글 개수를 반환하는 코드 개선
  10. 스토어 로그인 관련 개선
  11. 버그 수정


5.8
----------------------------------

  1. kboard_settings 필터 추가
  2. kboard_localize_strings 필터 추가
  3. kboard_pre_content_list_update 액션 추가
  4. kboard_content_list_update 액션 추가
  5. kboard_list_sorting_types 필터 추가
  6. kboard_document_print_head 액션 추가
  7. 대시보드에 다운로드 방식 변경 기능 추가 (PC에 저장하기 또는 가능한 경우 브라우저에서 읽기)
  8. 글쓰기 페이지에서 계층형 카테고리 선택 번역 추가
  9. 게시판 관리자의 글에만 숏코드(Shortcode) 실행할 수 있도록 고급설정 추가
  10. 스킨 폰트 사이즈 변경
  11. 스킨 시스템 개선 (입력필드 템플릿 추가)
  12. 인쇄 템플릿 코드 개선
  13. 관리자 페이지에 KBoard 파트너 메뉴 추가
  14. 버그 수정


5.7
----------------------------------

  1. kboard_skin_active_list 필터 추가
  2. kboard_list_select_count 필터 추가
  3. kboard_builtin_pg_list 필터 추가
  4. kboard_builtin_pg_init 액션 추가
  5. kboard_builder_get_list 필터 추가
  6. KBoard 미디어 추가 업로드 확장자 추가 (pjp,pjpeg,jfif,svg,bmp,webp,ico)
  7. 필수 입력필드 유효성 검사 코드 개선
  8. 대량관리 CSV 파일 업로드 코드 개선 (게시글 업데이트 관련)
  9. 전체 게시글 메뉴 기능 개선
  10. 전체 게시글 검색 기능 개선
  11. 발행된 게시글만 첨부파일 다운로드 가능하도록 개선
  12. 이전 다음 게시글 가져오기 관련 일부 개선
  13. 최대 게시글 작성 제한 권한 추가 (동일 사용자가 작성 가능한 게시글 수를 제한)
  14. 최소 시간 간격 권한 추가 (연속적으로 게시글을 작성하는 것을 방지)
  15. 버그 수정


5.6
----------------------------------

  1. kboard_reply_list_select 필터 추가
  2. kboard_reply_list_from 필터 추가
  3. kboard_reply_list_where 필터 추가
  4. kboard_reply_list_orderby 필터 추가
  5. kboard_content_next_uid_category1 필터 추가
  6. kboard_content_next_uid_category2 필터 추가
  7. kboard_content_prev_uid_category1 필터 추가
  8. kboard_content_prev_uid_category2 필터 추가
  9. kboard_pagination_sliding_size 필터 추가
  10. kboard_pagination_text 필터 추가
  11. kboard_pre_iframe_head 필터 추가
  12. kboard_content_status_list 필터 추가
  13. kboard_get_list_status 필터 추가
  14. kboard_is_reader 필터 추가
  15. kboard_is_writer 필터 추가
  16. kboard_is_editor 필터 추가
  17. kboard_is_order 필터 추가
  18. kboard_is_reply 필터 추가
  19. kboard_is_buyer 필터 추가
  20. kboard_is_attachment_download 필터 추가
  21. kboard_is_vote 필터 추가
  22. contentUpdate 함수에 kboard_pre_document_update 액션 추가
  23. 워드프레스 통합검색 관련 개선 (게시글 상태 휴지통일 때 Post 상태도 휴지통으로 적용)
  24. 유튜브, 비메오 동영상 URL을 iframe 코드로 변환하는 코드 개선
  25. session start 관련 개선
  26. KBoard 메일 메시지 템플릿 코드 개선
  27. 관리자 페이지 스킨 목록에서 .git 폴더 제외
  28. 본인 수정/삭제를 막을 수 있는 게시판 옵션 추가 (Alghost, https://github.com/Alghost)
  29. 서버의 저장공간을 절약할 수 있도록 이미지 최적화 기능 추가
  30. 버그 수정


5.5
----------------------------------

  1. kboard_url_board_list 필터 추가
  2. kboard_skin_contact_form_list_switch 필터 추가
  3. kboard_skin_contact_form_document_switch 필터 추가
  4. kboard_content_get_attachment_list 필터 추가
  5. kboard_content_editor_list 필터 추가
  6. kboard_content_editor_vars 필터 추가
  7. kboard_content_editor 필터 추가
  8. kboard_notice_list_orderby 필터 추가
  9. 공지사항 글에 댓글 사용 설정 추가
  10. KBoard 미디어 추가 기능 개선 (드래그 앤 드롭으로 이미지 추가하기)
  11. 스킨 기능 개선
  12. 썸머노트(Summernote) 에디터 추가
  13. 최신글 숏코드 기능 추가 및 사용성 개선 (최신글 모아보기 숏코드도 동일)
  14. 버그 수정


5.4.2
----------------------------------

  1. 버그 수정


5.4.1
----------------------------------

  1. 이메일 알림 전송 br 태그 관련 버그 수정


5.4
----------------------------------

  1. kboard_name_filter_message 필터 추가
  2. kboard_content_filter_message 필터 추가
  3. kboard_allowed_board_id 필터 추가 (게시판 모아보기 기능에 활용)
  4. kboard_skin_file_path 필터 추가
  5. kboard_router_board_url 필터 추가
  6. kboard_router_content_url 필터 추가
  7. kboard_content_value 필터 추가
  8. kboard_content_get_thumbnail_size 필터 추가
  9. kboard_content_get_thumbnail 필터 추가
  10. kboard_content_file_info 필터 추가
  11. kboard_content_file_metadata 필터 추가
  12. kboard_content_media_metadata 필터 추가
  13. kboard_comments_media_list 필터 추가
  14. kboard_iamport_endpoint_error_msg 필터 추가
  15. kboard_sales_default_date_range 필터 추가
  16. kboard_per_rss 필터 추가
  17. 금지단어가 있는 게시글 차단 메시지 설정 추가
  18. 검색엔진최적화(SEO) 관련 개선
  19. 스킨 스타일 개선
  20. 검색 필드 이스케이프 추가
  21. 아이프레임으로 보기 기능 개선
  22. 판매조회 관련 기능 개선
  23. 첨부파일 다운로드 코드 개선
  24. 이메일 알림 내용에 본문 이미지 추가
  25. 최신글 이메일 알림 첨부파일 포함 설정 추가
  26. 계층형 카테고리 코드 개선
  27. KBoard 미디어 추가 업로드 버튼 개선
  28. 버그 수정


5.3.11
----------------------------------

  1. 일부 데이터베이스에 이모지(emoji) 입력 안되는 문제 개선
  2. 금지단어가 있는 게시글 차단 기능 개선
  3. 게시판별 RSS 주소 기능 추가
  4. kboard_mail() 함수 추가
  5. 글쓰기 아이프레임으로 보기 기능 추가 (워드프레스 내장 에디터 깨질 때 사용 가능)
  6. kboard_ajax_builder 기능 개선
  7. kboard_content_execute_pre_redirect 액션 추가
  8. kboard_latest_alerts_attachments 필터 추가
  9. kboard_builder_view_iframe 필터 추가
  10. kboard_builder_set_skin 필터 추가
  11. kboard_order_update_value 필터 추가
  12. kboard_content_list_total_count 필터 추가
  13. kboard_content_list_items 필터 추가
  14. kboard_content_media_list 필터 추가
  15. kboard_view_iframe 필터 추가
  16. 버그 수정


5.3.10
----------------------------------

  1. 스킨 코드 개선
  2. kboard_pre_download_file 필터 추가
  3. kboard_skin_latestview_list 필터 추가
  4. kboard_pre_file_download 액션 추가
  5. kboard_skin_editor_header_before 액션 추가
  6. kboard_skin_editor_header_after 액션 추가
  7. kboard_skin_editor_header 필터 추가
  8. kboard_pending_approval_title 필터 추가
  9. kboard_pending_approval_content 필터 추가
  10. kboard_builder_mod() 함수 추가
  11. 첨부파일 다운로드 권한 추가
  12. 번역 추가
  13. 버그 수정


5.3.9
----------------------------------

  1. 버그 수정


5.3.8
----------------------------------

  1. kboard_add_media_url 필터 추가
  2. kboard_content_is_attached 필터 추가
  3. kboard_content_title_allowable_tags 필터 추가
  4. 입력필드 설정 기능 개선
  5. 전체 게시글 메뉴 기능 개선
  6. 버그 수정


5.3.7
----------------------------------

  1. 게시판 입력필드 설정 기능 추가
  2. kboard_resize() 함수 버그 수정
  3. kboard_content_is_new 필터 추가
  4. kboard_add_media_head 액션 추가
  5. kboard_admin_default_fields 필터 추가
  6. kboard_admin_extends_fields 필터 추가
  7. kboard_skin_fields 필터 추가
  8. kboard_get_template_field_data 필터 추가
  9. kboard_get_template_field_html 필터 추가
  10. kboard_document_add_option_value_separator 필터 추가
  11. kboard_document_add_option_value_field_data 필터 추가
  12. kboard_document_add_option_value_field_html 필터 추가
  13. kboard_skin_field_before 액션 추가
  14. kboard_skin_field_after 액션 추가
  15. kboard_current_user_roles 필터 추가
  16. 업데이트 기능 개선
  17. customer 스킨 삭제
  18. 버그 수정


5.3.6
----------------------------------

  1. 계층형 카테고리 기능 버그 수정


5.3.5
----------------------------------

  1. kboard_password_confirm 필터 추가
  2. kboard_content_like 액션 추가
  3. kboard_content_unlike 액션 추가
  4. kboard_content_paragraph_breaks 필터 추가
  5. kboard_password_confirm_reauth 필터 추가
  6. kboard_content_date 필터 추가
  7. kboard_pre_document_insert 액션 추가
  8. kboard_pre_document_update 액션 추가
  9. kboard_pre_document_delete 액션 추가
  10. kboard_pre_content_execute 액션 추가
  11. kboard_content_execute 액션 추가
  12. kboard_skin_editor_option 액션 추가
  13. 최신글 숏코드 기간 설정 기능 추가
  14. 계층형 카테고리 기능 추가
  15. 댓글보기권한 추가
  16. 버그 수정


5.3.4
----------------------------------

  1. 다수의 helper 함수에 필터 추가
  2. kboard_history_search_option 필터 추가
  3. kboard_sales_search_option 필터 추가
  4. kboard_sales_analytics_option 필터 추가
  5. kboard_always_view_list 필터 추가
  6. kboard_skin_always_view_list 액션 추가
  7. 버그 수정


5.3.3
----------------------------------

  1. 게시판 숏코드에 blog 옵션 추가
  2. kboard_cannot_read_document 액션 추가
  3. kboard_cannot_download_file 액션 추가
  4. kboard_file_download 액션 추가
  5. kboard_order_execute 액션 추가
  6. kboard_list_date_range 필터 추가
  7. kboard_list_search_option 필터 추가
  8. kboard_order_item_update_action 필터 추가
  9. kboard_order_default_value 필터 추가
  10. kboard_order_cancel_action 필터 추가
  11. kboard_latest_alerts_subject 필터 추가
  12. kboard_latest_alerts_message 필터 추가
  13. kboard_currency_format 필터 추가
  14. kboard_obfuscate_name 필터 추가
  15. 다양한 검색 옵션 추가 (검색 기능 강화)
  16. 게시판별 포인트 설정 기능 추가
  17. 게시판별 게시글 대량 관리 기능 추가 (CSV 파일 업로드)
  18. 답글쓰기권한 추가
  19. 추천권한 추가 (좋아요, 싫어요)
  20. 최고관리자그룹 선택 기능 추가
  21. 버그 수정


5.3.2
----------------------------------

  1. kboard_use_captcha 필터 추가
  2. kboard_list_select 필터 추가
  3. kboard_user_display 필터 추가
  4. 버그 수정


5.3.1
----------------------------------

  1. 대시보드에 게시글 바로 삭제 설정 추가
  2. 버그 수정


5.3
----------------------------------

  1. 게시판 숏코드에 카테고리 옵션 추가
  2. kboard_skin_header 액션 추가 (게시판 스킨 출력전에 실행)
  3. kboard_skin_footer 액션 추가 (게시판 스킨 출력후에 실행)
  4. 비밀글 작성 개선
  5. 스킨 디자인 레이아웃 변경
  6. KBLatestviewListTable 클래스 추가
  7. myCRED 플러그인 버그 수정 (포인트 기능)
  8. 게시글 관리자 승인 기능 추가
  9. 게시글 휴지통 기능 추가
  10. 게시판 기본 화면 고급설정 추가
  11. 글 쓴 후 이동 화면 고급설정 추가
  12. 최신글 뷰에 정렬 순서 설정 추가
  13. 관리자 페이지에서 게시판 보기 기능 추가
  14. kboard_iframe_head 액션 추가 (고유주소 또는 아이프레임으로 접근시 실행)
  15. 작성자/본문/제목 금지단어 기능 추가
  16. 구글 reCAPTCHA 적용
  17. Contact Form 스킨 추가
  18. 버그 수정


5.2
----------------------------------

  1. KBoard 미디어 추가 기능 개선
  2. kboard_download_file 필터 추가
  3. kboard_visible_comments 필터 추가
  4. kboard_skin_list 필터 추가
  5. RSS 버그 수정
  6. 관리자 페이지에서 전체 게시글 관리 기능 개선
  7. 리스트 항상 보기 기능 추가 (글 읽기 화면 하단에 게시판 리스트 보여주기)
  8. 새글 New 표시 및 시간 설정 기능 추가
  9. 최신글에 공지사항도 섞어서 표시되도록 변경
  10. 최신글 숏코드에 카테고리 옵션 추가
  11. 최대 첨부파일 개수 설정 기능 추가
  12. 커스텀 CSS 및 대시보기 기능 다수 추가
  13. 좋아요, 싫어요 기능 추가
  14. CAPTCHA 기능 비활성화 기능 추가
  15. 인쇄버튼 및 기능 추가
  16. kboard_list_default_sorting 필터 추가
  17. 버그 수정


5.1
----------------------------------

  1. KBoard 미디어 추가 기능 모바일 화면 최적화
  2. 아이프레임 허용 주소에 투도우(tudou), 유쿠(youku) 추가
  3. 게시판 리스트에서 소셜댓글 카운터 표시 기능 개선
  4. 고급설정에 전환추적을 위한 코드 입력 기능 추가
  5. 권한설정에 워드프레스 역할(Role) 직접선택 기능 추가
  6. 포인트기능 추가 (myCRED 플러그인 사용)
  7. KBoard 미디어 추가 기능 개선 (여러 파일 동시 업로드 가능)
  8. 워드프레스 상단 툴바에 게시판 설정페이지 바로가기 링크를 추가
  9. KBoard 자체 SEO 기능 최적화 (Yoast SEO, All In One SEO Pack 플러그인 대응)
  10. kboard_extends_setting 액션과 필터 추가
  11. kboard_thumbnail_size 필터 추가
  12. kboard_upload_extension 필터 추가
  13. kboard_uploaded_file 필터 추가
  14. KBContent 클래스에 getThumbnail() 메소드 추가
  15. 버그 수정


5.0
----------------------------------

  1. 스킨 CSS 수정
  2. kboard_xssfilter 설정 변경
  3. kboard_safeiframe 허용 도메인 추가
  4. jQuery.fn 으로 확장하는 함수끼리의 충돌 막기 (mgsmurf, https://github.com/mgsmurf)
  5. 글작성시 본문에 이미지 삽입하기 기능 추가
  6. KBController 클래스에 기능 추가
  7. 버그 수정


4.9
----------------------------------

  1. 소셜댓글 플러그인 연동 방법 변경 (멀티사이트 테스트 완료)
  2. 최신글 리스트에서 게시물에 달린 댓글숫자 표시
  3. 버그 수정


4.8
----------------------------------

  1. KBController 클래스 추가
  2. 글 작성시 올바르게 등록했는지 검증작업을 추가해 보안 강화
  3. 버그 수정


4.7
----------------------------------

  1. 백업 기능 버그 수정
  2. kboard_admin_menu 워드프레스 액션 추가
  3. 스킨 CSS 최적화
  4. 관리자 페이지에 전체 게시글 보기 페이지 추가
  5. 전체 게시글 게시판 이동 기능 추가
  6. 게시글 읽기 페이지에서 로그인 완료 후 되돌아오도록 리다이렉트 주소 추가
  7. 이메일 알림 mail() 함수에서 wp_mail() 함수로 교체
  8. 이메일 알림 내용에 등록된 게시글 링크 추가
  9. 비밀글의 읽기 및첨부파일 다운로드 관련 버그 수정
  10. 첨부파일 확장자 소문자로 변경해서 저장
  11. kboard_iframe_whitelist 워드프레스 필터 추가
  12. 대시보드에 첨부파일 다운로드 깨짐 방지 기능 활성화 추가
  13. 게시판 설정에 소셜댓글 플러그인 연동 옵션 추가
  14. kboard_insert_data 워드프레스 필터 추가
  15. kboard_update_data 워드프레스 필터 추가
  16. 버그 수정


4.6
----------------------------------

  1. 최신글 이메일 알림시 메일 제목에 보드명 추가 (blauen, https://www.cosmosfarm.com/threads/document/3025)
  2. 아이프레임 허용 주소에 슬라이드쉐어(SlideShare), 네이버 tvcast 추가
  3. 기본스킨 CSS 최적화
  4. 필터와 액션에 게시판 ID값 넘겨주도록 기능 추가 (설명: https://www.cosmosfarm.com/products/kboard/hooks)
  5. kboard_document_delete 액션에 게시글 번호 넘겨주도록 수정돼 사용법 변경됨
  6. 고급 사용자용 고유주소 변경
  7. 아이프레임으로 보기 기능 추가 (원페이지 테마 또는 게시판이 심하게 깨질 때 사용)
  8. 이미지 사이즈 변경하는 kboard_resize 함수 추가 및 thumbnail 스킨에 적용 완료
  9. 댓글쓰기권한 추가
  10. 버그 수정


4.5
----------------------------------

  1. 게시판 설정에서 댓글 사용 비활성화시 댓글 스킨 정보를 DB에서 삭제해 불필요한 로딩 없앰
  2. 비밀글에 관리자가 답글 쓸 때 부모글 비밀번호 가져오도록 기본 스킨 수정
  3. WPDB 클래스로 쿼리 교체
  4. kboard_board_content 테이블 content 데이터형 longtext로 변경
  5. kboard_board_content 테이블 like 컬럼 생성
  6. kboard_after_executing_url 워드프레스 필터 추가
  7. kboard_default_build_mod 워드프레스 필터 추가
  8. 버그 수정


4.4
----------------------------------

  1. 스킨 스타일 수정
  2. KBCaptcha 클래스 기능 개선
  3. 업로드 허용 파일 확장자 추가 (7z, xlsx, pptx, docx)
  4. 관리자 대시보드 페이지에 시스템 설정 추가
  5. 답글쓰기 기능 개선
  6. 게시판에서 사용 중인 스킨의 style.css 파일만 헤더에 추가 되도록 개선 (페이지 로딩속도 개선)
  7. 파일 업로드시 발생된 오류 Alert창으로 알림
  8. 답글 작성시 원글 내용 표시하는 설정 추가
  9. 버그 수정


4.3
----------------------------------

  1. 일본어 지원 (외부 기여자에게 일본어 번역 제공 받음)
  2. 버그 수정


4.2
----------------------------------

  1. xssfilter에서 사용 가능한 태그의 제한을 완화함 (사용 가능 태그 증가)
  2. 스킨 파일 내부에서 style.css 파일을 불러오기 위한 link 태그 제거하고 head 영역에서 style.css 파일 로딩
  3. Font Awesome 3.2.1 추가
  4. 게시글에 대한 답글쓰기 기능 추가
  5. 스킨의 편리한 설치를 위해서 스토어 기능 추가
  6. 버그 수정


4.1
----------------------------------

  1. 자동링크 서버파일 확장자까지 링크 안걸리는 버그 수정
  2. table이 깨지는 현상 fix (Wooram Jun, https://github.com/chatii2412)
  3. 최신글 이메일 알림에서 작성글 제목, 내용도 함께 보내지도록 수정
  4. 게시판 customer스킨 개인정보 필드값 안지워지는 버그 수정 (옵션 필드값 버그 수정)
  5. KBCaptcha 클래스 나눔고딕 폰트 사용 제거 및 보안코드 이미지 생성 실패시 ERROR 이미지로 대체 표시해 기능 개선
  6. xssfilter에서 안전한 태그와 속성 허용
  7. 모든 PHP 문자열 echo로 출력해 short_open_tag옵션 비활성화시 오류 방지
  8. 고급 사용자용 고유주소 기본 비활성화
  9. kboard_list_where, kboard_list_orderby 워드프레스 필터 추가 및 외부 플러그인 제작에 사용 가능
  10. HTML Purifier 4.6.0 버전으로 교체
  11. 워드프레스 네트워크 기능 버그 수정 및 기능 개선
  12. 워드프레스 ajax로 kboard_ajax_builder 요청시 해당 게시판 리스트 불러오기 기능 추가 (스킨 제작시 사용 가능)
  13. 버그 수정


4.0
----------------------------------

  1. 기본스킨 CSS 최적화
  2. 기본스킨 검색옵션 search -> target 이름 변경
  3. 영문 지원 (Bing Translator 사용)
  4. 버그 수정


3.9
----------------------------------

  1. 기본스킨 CSS 최적화
  2. 아이프레임 허용 주소에 비메오(Vimeo), 사운드클라우드(SoundCloud) 추가
  3. kboard_document_insert, kboard_document_update, kboard_document_delete 워드프레스 액션 훅 추가 및 외부 플러그인 제작에 사용 가능
  4. 버그 수정


3.8
----------------------------------

  1. 레이아웃 깨짐 방지 개선


3.7
----------------------------------

  1. 비밀글 및 권한 버그 수정
  2. 비밀글 작성시 바로 인증과정을 거쳐서 비밀번호 확인창 숨김
  3. 버그 수정


3.6
----------------------------------

  1. 메모리 최적화


3.5
----------------------------------

  1. KBRouter 클래스 추가, 게시글 번호로 게시판이 설치된 페이지를 찾고 이동함
  2. RSS 기능 강화
  3. kboard_board_content 테이블 search 컬럼 생성
  4. 게시물 통합검색(워드프레스검색) 지원
  5. 기본스킨에 통합검색 등록 옵션 추가
  6. 기본스킨에 이전글, 다음글 버튼 추가
  7. 게시판 관리에 게시글 본문 숏코드(Shortcode) 실행 옵션 추가
  8. kboard_content 필터 추가로 게시글 본문내용을 편집하는 외부 플러그인 제작 가능
  9. 최신글 뷰(여러 게시판 모아보기) 기능 추가
  10. 선택된 관리자 비밀글 읽지 못하는 버그 수정
  11. 자동링크 활성화/비활성화 게시판 관리에 옵션 추가
  12. 버그 수정


3.4
----------------------------------

  1. 보안 취약점 수정
  2. 로그인 페이지 이동 wp-login.php -> wp_login_url() 변경


3.3
----------------------------------

  1. 기본스킨 크로스 브라우징 지원 강화
  2. 게시판 관리에 특정 테마 레이아웃 깨짐 방지 옵션 추가


3.2
----------------------------------

  1. KBCaptcha 클래스 변경


3.1
----------------------------------

  1. XSS 보안 레벨 적정 수준 조절


3.0
----------------------------------

  1. 활성화 오류 수정


2.9
----------------------------------

  1. kboard_board_meta 테이블 value 데이터형 text로 변경


2.8
----------------------------------

  1. board.php에 wp_head(), wp_footer() 추가로 버그 수정
  2. 관리자 게시판 설정페이지 용어 수정
  3. KBCaptcha 클래스 추가
  4. 스킨 스타일 수정
  5. 본문 기본양식 지원


2.7
----------------------------------

  1. 플러그인 삭제 버그 수정
  2. 플러그인 활성화 버그 수정
  3. 대시보드에 최신버전 히스토리 바로가기 버튼 추가


2.6
----------------------------------

  1. include 버그 수정


2.5
----------------------------------

  1. 게시판 고유주소 페이지에서 테마를 제거하고 KBoard만 출력
  2. KBSeo 클래스 추가 및 검색 엔진 최적화 강화
  3. mysql_insert_id() 값이 없을때 LAST_INSERT_ID() 실행 하도록 업데이트
  4. KBMail 클래스에서 wp_mail() 함수 제거하고 mail() 함수로 변경
  5. 기본 스킨 워드프레스 3.6 twentythirteen 테마 호환성 강화
  6. HTML Purifier 4.5.0 추가로 Cross-site scripting (XSS) 공격에 대한 보안 강화
  7. DB 테이블 이름에 PREFIX 추가로 워드프레스 멀티사이트 지원


2.4
----------------------------------

  1. description 버그 수정


2.3
----------------------------------

  1. 업데이트 기능 추가
  2. 대시보드 페이지 추가
  3. 백업 및 복구 기능 추가
  4. Content 클래스에 getCommentsCount() 메소드 추가 및 스킨에 적용
  5. KBoard 클래스에 buildComment() 메소드 추가 및 스킨에 적용
  6. 스킨의 게시물 리스트에 비밀글 아이콘 표시
  7. 댓글 스킨 선택 가능
  8. SEO 최적화, description 메타태그에 게시물 내용 표시
  9. 스킨 CSS 수정
  10. 버그 수정


2.2
----------------------------------

  1. 스킨 스타일을 테마와 호완성 높이기 위해서 일부 수정
  2. 워드프레스 페이지에 게시판 자동 추가 기능 도입
  3. 관리자 화면 수정
  4. 버그 수정


2.1
----------------------------------

  1. 버그 수정


2.0
----------------------------------

  1. 유튜브 공식사이트 공유하기 코드 입력 가능
  2. 비밀글, 비회원 작성글 수정 및 삭제시 비밀번호 확인 한 번만 하도록 변경
  3. DB에 kboard_board_meta 테이블 추가
  4. KBoardMeta 클래스 추가 (게시판의 다양한 설정을 저장하고 읽어오는 클래스)
  5. 최신글 등록시 이메일 알림 기능 및 KBMail 클래스 추가
  6. DB 질의 실행시 오류가 발생하면 오류 내용 출력 후 중단 및 kboard_query 함수 추가
  7. 버그 수정


1.9
----------------------------------

  1. 윈도우 OS 환경 지원
  2. 최신글 리스트 URL 버그 수정
  3. 비밀글 못 읽는 버그 수정


1.8
----------------------------------

  1. avatar 스킨 추가 - 작성자 이름 대신 아바타 이미지 표시
  2. customer 스킨 추가 - 이름, 연락처 입력 가능
  3. 최신글 리스트 생성 기능 추가
  4. 작성자 검색 기능 추가
  5. 버그 수정


1.7
----------------------------------

  1. 기본스킨에 제목, 비밀번호 등 Validation 스크립트 추가로 저장 전에 폼 데이터 확인
  2. 공지사항은 권한 상관없이 누구나 글 읽기 가능
  3. KBoard 댓글 플러그인 필수 설치 경고창 추가
  4. 기본스킨에 향상된 반응형 웹(Responsive web) 적용
  5. 버그 수정


1.6
----------------------------------

  1. 실제 경로와 서버의 DOCUMENT ROOT 값이 다를 때에도 정상적으로 동작 가능
  2. KBoardSkin 클래스 싱글톤 패턴(Singleton Pattern) 적용
  3. 기본 스킨의 리스트 페이지에서 썸네일 삭제하고 썸네일 스킨 추가
  4. 보안 : iframe 주소 화이트리스트 기능 추가
  5. 버그 수정


1.5
----------------------------------

  1. BoardBuilder() 클래스 버그 수정
  2. ContentList() 클래스 버그 수정
  3. 메소드 추가 : ContentList::init()
  4. 메소드 추가 : ContentList::initWithRSS()
  5. 통합 RSS 피드 기능 추가 : 게시물 피드 공개. 구글, 야후, Bing 등 검색엔진에 Sitemaps 제출이 가능.
  6. 게시판 스킨 document.php 기능 추가 : schema.org를 이용한 콘텐츠 마크업을 적용. SEO 대폭 상승 (참고:schema.org)
  7. 게시판 스킨 list.php 기능 추가 : powered by


1.4
----------------------------------

  1. 파일업로드 확장자 체크 버그 수정
  2. 파일명 변경 : Pagination.function.php -> Pagination.helper.php
  3. 파일 추가 : Security.helper.php
  4. 보안 강화


1.3
----------------------------------

  1. 같이 압축되어 있던 kboard, kboard-comments 플러그인들을 각각 압축해서 배포함
  2. 클래스 추가 : Comment()
  3. 클래스명 변경 : Comments() -> CommentList()
  4. 클래스명 변경 : Skin() -> KBoardSkin()
  5. 클래스명 변경 : Board() -> KBoard()
  6. 메소드 추가 : ContentList::hasNextNotice()
  7. 메소드 삭제 예정 : ContentList::hasNoticeNext()
  8. 게시판 고유주소 기능 강화
  9. 비회원 댓글 작성 가능
  10. 관리자 페이지에서 전체 댓글 리스트 관리
  11. 게시판 및 댓글 기본스킨 CSS 업데이트
  12. kboard_pagination 함수 및 페이징 CSS 업데이트


1.2
----------------------------------

  1. 자잘한 버그 수정


1.1
----------------------------------

  1. 워드프레스가 루트디렉토리가 아닌 서브디렉토리에 있을때 게시판 생성 가능
  2. 글 수정시 읽은숫자 초기화 문제 수정

