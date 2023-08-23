# -*- coding: utf-8 -*-

# 버전차이 큼
# https://selenium-python.readthedocs.io/api.html

from lib2to3.pgen2.tokenize import tokenize
from time import sleep
import selenium
# from pyvirtualdisplay import Disply
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.alert import Alert
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.support.ui import WebDriverWait

from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By


from selenium.common import exceptions
#['ElementClickInterceptedException', 'ElementNotInteractableException', 'ElementNotSelectableException', 'ElementNotVisibleException', 'ErrorInResponseException', 'ImeActivationFailedException', 'ImeNotAvailableException', 'InsecureCertificateException', 'InvalidArgumentException', 'InvalidCookieDomainException', 'InvalidCoordinatesException', 'InvalidElementStateException', 'InvalidSelectorException', 'InvalidSessionIdException', 'InvalidSwitchToTargetException', 'JavascriptException', 'MoveTargetOutOfBoundsException', 'NoAlertPresentException', 'NoSuchAttributeException', 'NoSuchCookieException', 'NoSuchElementException', 'NoSuchFrameException', 'NoSuchWindowException', 'RemoteDriverServerException', 'ScreenshotException', 'SessionNotCreatedException', 'StaleElementReferenceException', 'TimeoutException', 'UnableToSetCookieException', 'UnexpectedAlertPresentException', 'UnexpectedTagNameException', 'UnknownMethodException', 'WebDriverException']


# object_methods = [method_name for method_name in dir(selenium.common.exceptions)
                #   if callable(getattr(selenium.common.exceptions, method_name))]


# --------------------------------------------------------------------------------
# 파라미터 받거나 하는부분
host = 'http://211.37.179.64'
login = host+'/ws/auth/'

# 로그아웃 빼고 실행
ignore = ['logout']


# --------------------------------------------------------------------------------





# 옵션 세팅
chrome_options = Options()
chrome_options.add_argument('--headless')
# chrome_options.add_argument('--disable-dev-shm-usage')
chrome_options.add_argument('--no-sandbox')
chrome_options.add_experimental_option('w3c', False)


capa = DesiredCapabilities.CHROME.copy()
capa = DesiredCapabilities.HTMLUNITWITHJS .copy()
# capa['javascriptEnabled'] = True

# 브라우저 실행
driver = webdriver.Chrome(
    executable_path = '/usr/local/bin/chromedriver',
    chrome_options=chrome_options,
    desired_capabilities=capa
)

# 브라우저 크기 세팅
driver.set_window_position(0, 0)
driver.set_window_size(1920, 1080)

# 타임아웃 세팅 전체에 거는거라 시간 널널하게 하거나 안하기
# driver.set_page_load_timeout(5)


# js 에러 담기
jserror = {}

# 로그인
try:
    
    driver.get(login)
    driver.implicitly_wait(1)
    driver.find_element_by_css_selector('input[name=id]').send_keys("admin")
    driver.find_element_by_css_selector('input[name=pw]').send_keys("admin")
    driver.find_element_by_css_selector('form button').click()
    driver.implicitly_wait(1)

    # 로그인 후 페이지 기준
    main = driver.current_url

    for t in driver.log_types:
        for m in driver.get_log(t):
            jserror[main] = m
except exceptions as e:
    print(e.message)
    print('로그인 실패')
    exit()



menu = []


'''
# 모든 html 클릭하고 이동되는데서도 클릭하고
# 로그아웃되는거 어떻게든 해야함
def recursive(link=''):
    current = driver.current_url
    for e in driver.find_elements_by_css_selector('*'):

        if e.is_displayed():
            driver.implicitly_wait(5)
            e.click()
        move = driver.current_url

        print(current == move)
        # if current != move:
        #     recursive(move);
try:
    recursive()
finally:
    driver.quit()
driver.quit()
exit()
'''



# a태그 전체
# 이동된 페이지에 없으면 에러
# 로그아웃 빼고 해야함
for a in driver.find_elements_by_css_selector('a'):
    href = a.get_property('href')
    l = href.split('/').pop()
    if ( l in ignore ):
        continue
    menu.append(href)

for m in menu:
    driver.implicitly_wait(2)

    # alert 창 확인, 닫기
    try:
        WebDriverWait(driver,1).until(EC.alert_is_present())
        driver.switch_to.alert.dismiss()
    except:
        print('no alert')

    print(m)
    driver.get(m)
    for t in driver.log_types:
        for log in driver.get_log(t):
            jserror[main] = log
    
    l = m.split('/')
    l = '_'.join(l[3:])
    driver.implicitly_wait(2)
    # print(l+'.png')
    # driver.save_screenshot(l+'.png')



# 스샷 사이즈는 따로 확인해야함
# driver.get_screenshot_as_file('test.png')
# driver.save_screenshot('test.png')

# 전부 루프
# print(driver.page_source)

# 태그만 스샷
# element.screenshot('/Screenshots/foo.png')


# print(driver.get_log())
driver.implicitly_wait(1)


if len(jserror) > 0:
    print('js error')
    print(jserror)

print('끝')

# 뒤로가기
# driver.forward()


# 탭닫기
driver.close()

# 브라우저 닫기
driver.quit()


# login_button = driver.find_element_by_class_name("buttonLogin")

# 쿼리셀렉터
# driver.find_element_by_css_selector('input[ng-model="formData.address.address1"]')

# driver.execute_script("arguments[0].value = 'arguments[1]';", elm, value)
# .click()




# input 입력하기
# search_bar.clear()
# search_bar.send_keys("getting started with python")

# search_bar.send_keys(Keys.RETURN)
# search_bar.send_keys(Keys.ENTER) - 이 방식도 위와 동일하게 작용




# driver.switch_to_window(chrome_taps[1])
# chrome_taps = driver.window_handles
# chrome_taps ['CDwindow-5313D10AD7A8C5CA3923943C5FDA60EE', 'CDwindow-655E6A7B0EA39AE233DF8F997515FEBC']
