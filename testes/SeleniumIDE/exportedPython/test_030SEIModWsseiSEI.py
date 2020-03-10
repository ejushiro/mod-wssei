# Generated by Selenium IDE
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class Test030SEIModWsseiSEI():
  def setup_method(self, method):
    self.driver = webdriver.Remote(command_executor='http://seleniumhub:4444/wd/hub', desired_capabilities=DesiredCapabilities.CHROME)
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def test_acompanhamentosGrupoCadastrar(self):
    self.driver.get("http://sei3.nuvem.gov.br/sip/login.php?sigla_orgao_sistema=ME&sigla_sistema=SEI&infra_url=L3NlaS8=")
    self.driver.find_element(By.ID, "txtUsuario").send_keys("teste")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("teste")
    self.driver.find_element(By.ID, "sbmLogin").click()
    self.driver.find_element(By.ID, "selInfraUnidades").click()
    dropdown = self.driver.find_element(By.ID, "selInfraUnidades")
    dropdown.find_element(By.XPATH, "//option[. = 'TESTE']").click()
    self.driver.find_element(By.LINK_TEXT, "Acompanhamento Especial").click()
    self.driver.find_element(By.ID, "btnGrupo").click()
    self.driver.find_element(By.CSS_SELECTOR, "#btnNovo > .infraTeclaAtalho").click()
    self.driver.find_element(By.ID, "txtNome").send_keys("Acompanhamento Grupo 1_0")
    self.driver.find_element(By.NAME, "sbmCadastrarGrupoAcompanhamento").click()
    self.driver.find_element(By.ID, "selInfraUnidades").click()
    dropdown = self.driver.find_element(By.ID, "selInfraUnidades")
    dropdown.find_element(By.XPATH, "//option[. = 'TESTE_1_2']").click()
    self.driver.find_element(By.LINK_TEXT, "Acompanhamento Especial").click()
    self.driver.find_element(By.ID, "btnGrupo").click()
    self.driver.find_element(By.CSS_SELECTOR, "#btnNovo > .infraTeclaAtalho").click()
    self.driver.find_element(By.ID, "txtNome").send_keys("Acompanhamento Grupo 1_2")
    self.driver.find_element(By.NAME, "sbmCadastrarGrupoAcompanhamento").click()
    self.driver.find_element(By.CSS_SELECTOR, "#lnkSairSistema > .infraImg").click()
    self.driver.close()
  
  def test_acompanhamentosGrupoCadastrarFlood(self):
    self.driver.get("http://sei3.nuvem.gov.br/sip/login.php?sigla_orgao_sistema=ME&sigla_sistema=SEI&infra_url=L3NlaS8=")
    self.driver.find_element(By.ID, "txtUsuario").send_keys("teste")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("teste")
    self.driver.find_element(By.ID, "sbmLogin").click()
    self.driver.find_element(By.ID, "selInfraUnidades").click()
    dropdown = self.driver.find_element(By.ID, "selInfraUnidades")
    dropdown.find_element(By.XPATH, "//option[. = 'TESTE_1_2']").click()
    for i in range(0, 40):
      WebDriverWait(self.driver, 30000).until(expected_conditions.presence_of_element_located((By.LINK_TEXT, "Acompanhamento Especial")))
      self.driver.find_element(By.LINK_TEXT, "Acompanhamento Especial").click()
      WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "btnGrupo")))
      self.driver.find_element(By.ID, "btnGrupo").click()
      WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.CSS_SELECTOR, "#btnNovo > .infraTeclaAtalho")))
      self.driver.find_element(By.CSS_SELECTOR, "#btnNovo > .infraTeclaAtalho").click()
      self.vars["data"] = self.driver.execute_script("var d= new Date(); var m=((d.getMonth()+1)<10)?\'0\'+(d.getMonth()+1):(d.getMonth()+1); return d.getFullYear()+\'-\'+m+\'-\'+d.getDate();")
      self.vars["hora"] = self.driver.execute_script("return (new Date().getHours()+\'-\' + new Date().getMinutes() + \'-\' + new Date().getSeconds())")
      WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "txtNome")))
      self.driver.find_element(By.ID, "txtNome").send_keys("Grupo " + self.vars["data"] + "-" + self.vars["hora"])
      self.driver.find_element(By.NAME, "sbmCadastrarGrupoAcompanhamento").click()
      time.sleep(1)
    self.driver.find_element(By.CSS_SELECTOR, "#lnkSairSistema > .infraImg").click()
    self.driver.close()
  
  def test_cargosCadastrarFlood(self):
    self.driver.get("http://sei3.nuvem.gov.br/sip/login.php?sigla_orgao_sistema=ME&sigla_sistema=SEI&infra_url=L3NlaS8=")
    self.driver.find_element(By.ID, "txtUsuario").send_keys("teste")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("teste")
    self.driver.find_element(By.ID, "sbmLogin").click()
    for i in range(0, 40):
      self.driver.find_element(By.XPATH, "//*[@id=\"main-menu\"]/li[1]/a").click()
      self.driver.find_element(By.LINK_TEXT, "Assinaturas das Unidades").click()
      self.driver.find_element(By.ID, "btnAdicionar").click()
      self.vars["data"] = self.driver.execute_script("var d= new Date(); var m=((d.getMonth()+1)<10)?\'0\'+(d.getMonth()+1):(d.getMonth()+1); return d.getFullYear()+\'-\'+m+\'-\'+d.getDate();")
      self.vars["hora"] = self.driver.execute_script("return (new Date().getHours()+\'-\' + new Date().getMinutes() + \'-\' + new Date().getSeconds())")
      self.driver.find_element(By.ID, "txtCargoFuncao").send_keys("Cargo "+ self.vars["data"] + "-" + self.vars["hora"])
      self.driver.find_element(By.ID, "txtUnidade").click()
      WebDriverWait(self.driver, 30000).until(expected_conditions.element_to_be_clickable((By.ID, "txtUnidade")))
      self.driver.find_element(By.ID, "txtUnidade").send_keys("teste")
      self.driver.find_element(By.CSS_SELECTOR, "body").click()
      self.driver.find_element(By.CSS_SELECTOR, ".infraButton:nth-child(1) > .infraTeclaAtalho").click()
      time.sleep(1)
    self.driver.find_element(By.CSS_SELECTOR, "#lnkSairSistema > .infraImg").click()
    self.driver.close()
  